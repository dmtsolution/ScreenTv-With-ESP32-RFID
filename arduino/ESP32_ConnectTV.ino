#include <SPI.h>
#include <MFRC522.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

#define SS_PIN 5
#define RST_PIN 22
MFRC522 rfid(SS_PIN, RST_PIN);

// Configuration WiFi
const char* ssid = "ADMINISTRATION";
const char* password = "@dm1n@LAN22";

// Serveur PHP (API) - IP du PC qui héberge l'application
const char* serverUrl = "http://10.153.10.175:8000/api/badge.php";

void setup() {
  Serial.begin(115200);

  // Connexion WiFi
  Serial.print("Connexion WiFi...");
  WiFi.begin(ssid, password);
  int c = 0;
  while (WiFi.status() != WL_CONNECTED && c < 20) {
    delay(500);
    Serial.print(".");
    c++;
  }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nConnecté au Wi-Fi !");
    Serial.print("IP: ");
    Serial.println(WiFi.localIP());
  } else {
    Serial.println("\nErreur WiFi");
  }

  SPI.begin();
  rfid.PCD_Init();
  Serial.println("Lecteur RFID pret");
}

void loop() {
  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
    delay(100);
    return;
  }

  // Lecture UID
  String uidStr = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    if (rfid.uid.uidByte[i] < 0x10) uidStr += "0";
    uidStr += String(rfid.uid.uidByte[i], HEX);
    if (i < rfid.uid.size - 1) uidStr += " ";
  }
  uidStr.toUpperCase();

  Serial.print("Badge detecte: ");
  Serial.println(uidStr);

  // Envoi UID au serveur PHP
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    String url = String(serverUrl) + "?uid=" + uidStr;
    http.begin(url);
    int httpCode = http.GET();

    if (httpCode == 200) {
      String response = http.getString();
      Serial.print("Reponse: ");
      Serial.println(response);
    } else {
      Serial.print("Erreur HTTP: ");
      Serial.println(httpCode);
    }
    http.end();
  }

  delay(3000);
  rfid.PICC_HaltA();
}