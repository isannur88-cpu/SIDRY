#include <WiFi.h>
#include <HTTPClient.h>
#include <WebServer.h>
#include <ESP32Servo.h>
#include "DHT.h"
#include "time.h"  // untuk NTP

#define DHTPIN 4
#define DHTTYPE DHT22

#define RAIN_PIN 34
#define SERVO_PIN 13

const char* ssid     = "uwu";
const char* password = "qwerty3th";

// NTP config - WIB = UTC+7
const char* ntpServer   = "pool.ntp.org";
const long  gmtOffset   = 25200;  // 7 jam x 3600 detik
const int   dstOffset   = 0;

DHT dht(DHTPIN, DHTTYPE);
Servo jemuran;
WebServer server(80);

bool manualMode = false;

unsigned long lastSave = 0;
const unsigned long saveInterval = 60000;

unsigned long lastLive = 0;
const unsigned long liveInterval = 3000;

const char* serverIP = "10.199.126.110";
const char* basePath = "/namanya/api/";

// ======================
// AMBIL JAM SEKARANG
// ======================

struct tm getWaktu() {
  struct tm timeinfo;
  if (!getLocalTime(&timeinfo)) {
    Serial.println("Gagal ambil waktu NTP");
  }
  return timeinfo;
}

// Konversi jam & menit ke menit total (buat perbandingan lebih mudah)
int toMenit(int jam, int menit) {
  return jam * 60 + menit;
}

// ======================
// CEK JADWAL JEMURAN
// ======================

// Return:
//  1  = jadwal keluar (pagi)
// -1  = jadwal masuk (sore)
//  0  = di luar jadwal terjadwal

int cekJadwal(struct tm t) {
  int sekarang = toMenit(t.tm_hour, t.tm_min);

  // Jam 08:00 - 09:00 → jemuran keluar
  if (sekarang >= toMenit(8, 0) && sekarang < toMenit(9, 0)) {
    return 1;
  }

  // Jam 16:30 - 18:00 → jemuran masuk
  if (sekarang >= toMenit(16, 30) && sekarang < toMenit(18, 0)) {
    return -1;
  }

  return 0;
}

// ======================
// WEB CONTROL
// ======================

void handleMasuk() {
  manualMode = true;
  jemuran.write(0);
  Serial.println("MANUAL : JEMURAN MASUK");
  server.send(200, "text/plain", "Jemuran Masuk");
}

void handleKeluar() {
  manualMode = true;
  jemuran.write(90);
  Serial.println("MANUAL : JEMURAN KELUAR");
  server.send(200, "text/plain", "Jemuran Keluar");
}

void handleAuto() {
  manualMode = false;
  Serial.println("AUTO MODE AKTIF");
  server.send(200, "text/plain", "Auto Mode");
}

// ======================

void kirimData(String endpoint, float suhu, float kelembaban, int rainValue, String statusHujan) {

  if (WiFi.status() != WL_CONNECTED) return;

  WiFiClient client;
  HTTPClient http;

  String url = "http://" + String(serverIP) + String(basePath) + endpoint;

  http.begin(client, url);
  http.addHeader("Content-Type", "application/json");

  String json =
    "{\"suhu\":"        + String(suhu, 2) +
    ",\"kelembaban\":"  + String(kelembaban, 2) +
    ",\"rain\":"        + String(rainValue) +
    ",\"status_hujan\":\"" + statusHujan + "\"}";

  int httpCode = http.POST(json);
  Serial.print(endpoint);
  Serial.print(" HTTP Code : ");
  Serial.println(httpCode);

  http.end();
}

void setup() {

  Serial.begin(115200);
  dht.begin();
  pinMode(RAIN_PIN, INPUT);
  jemuran.attach(SERVO_PIN);
  jemuran.write(90);

  WiFi.mode(WIFI_STA);
  WiFi.begin(ssid, password);

  Serial.print("Connecting");
  while (WiFi.status() != WL_CONNECTED) {
    Serial.print(".");
    delay(1000);
  }

  Serial.println();
  Serial.println("WiFi Connected");
  Serial.print("IP : ");
  Serial.println(WiFi.localIP());

  // Sync NTP
  configTime(gmtOffset, dstOffset, ntpServer);
  Serial.print("Sinkronisasi NTP");
  struct tm t;
  while (!getLocalTime(&t)) {
    Serial.print(".");
    delay(1000);
  }
  Serial.println();
  Serial.printf("Waktu OK: %02d:%02d:%02d\n", t.tm_hour, t.tm_min, t.tm_sec);

  server.on("/masuk", handleMasuk);
  server.on("/keluar", handleKeluar);
  server.on("/auto", handleAuto);
  server.begin();

  Serial.println("Web Server Ready");
}

void loop() {

  server.handleClient();

  float suhu       = dht.readTemperature();
  float kelembaban = dht.readHumidity();
  int   rainValue  = analogRead(RAIN_PIN);

  String statusHujan = (rainValue < 3000) ? "HUJAN" : "TIDAK HUJAN";

  if (isnan(suhu) || isnan(kelembaban)) {
    Serial.println("Failed to read DHT!");
    delay(2000);
    return;
  }

  struct tm waktuSekarang = getWaktu();
  int jadwal = cekJadwal(waktuSekarang);

  // ======================
  // MODE OTOMATIS
  // ======================

  if (!manualMode) {

    if (jadwal == 1) {
      // Jadwal pagi: keluar dulu, tapi tetap cek hujan
      if (statusHujan == "HUJAN") {
        jemuran.write(0);
        Serial.println("JADWAL PAGI tapi HUJAN : JEMURAN MASUK");
      } else {
        jemuran.write(90);
        Serial.println("JADWAL PAGI : JEMURAN KELUAR");
      }

    } else if (jadwal == -1) {
      // Jadwal sore: masuk tanpa peduli cuaca
      jemuran.write(0);
      Serial.println("JADWAL SORE : JEMURAN MASUK");

    } else {
      // Di luar jadwal: pakai logika sensor seperti biasa
      if (statusHujan == "HUJAN" || (suhu >= 20 && suhu <= 27)) {
        jemuran.write(0);
        Serial.println("AUTO : JEMURAN MASUK");
      } else {
        jemuran.write(90);
        Serial.println("AUTO : JEMURAN KELUAR");
      }
    }

  }

  Serial.printf("Jam: %02d:%02d | Suhu: %.1f | Kelembaban: %.1f | Rain: %d | %s\n",
    waktuSekarang.tm_hour, waktuSekarang.tm_min,
    suhu, kelembaban, rainValue, statusHujan.c_str());

  if (millis() - lastLive >= liveInterval) {
    lastLive = millis();
    kirimData("live.php", suhu, kelembaban, rainValue, statusHujan);
  }

  if (millis() - lastSave >= saveInterval) {
    lastSave = millis();
    kirimData("create.php", suhu, kelembaban, rainValue, statusHujan);
  }

}
