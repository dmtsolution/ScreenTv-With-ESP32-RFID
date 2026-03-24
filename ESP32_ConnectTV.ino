#include <SPI.h>
#include <MFRC522.h>
#include <WiFi.h>
#include <WiFiUdp.h>

#define SS_PIN 5
#define RST_PIN 22
MFRC522 rfid(SS_PIN, RST_PIN);

// --- CONFIGURATION DES RÉSEAUX WIFI ---
const char* ssid1 = "ADMINISTRATION";
const char* password = "@dm1n@LAN22";

WiFiUDP udp;
const char* computer_ip = "192.168.100.46";
const int udp_port = 5005;

void setup() {
  Serial.begin(115200);

  // 1. Essai sur le premier réseau (ouvert)
  Serial.print("Essai WiFi 1...");
  WiFi.begin(ssid1, password);
  int c = 0;
  while (WiFi.status() != WL_CONNECTED && c < 15) {
    delay(500);
    Serial.print(".");
    c++;
  }

  // 2. Si échec, essai sur le deuxième réseau (ouvert)
  // if (WiFi.status() != WL_CONNECTED) {
  //   Serial.print("\nEssai WiFi 2...");
  //   WiFi.begin(ssid2);
  //   c = 0;
  //   while (WiFi.status() != WL_CONNECTED && c < 15) {
  //     delay(500);
  //     Serial.print(".");
  //     c++;
  //   }
  // }

  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nConnecté au Wi-Fi !");
    Serial.println(WiFi.SSID());
  } else {
    Serial.println("\nAucun réseau trouvé.");
  }

  SPI.begin();
  rfid.PCD_Init();
}

void loop() {
  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) return;

  String uidStr = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    uidStr += (rfid.uid.uidByte[i] < 0x10 ? "0" : "");
    uidStr += String(rfid.uid.uidByte[i], HEX);
    if (i < rfid.uid.size - 1) uidStr += " ";
  }
  uidStr.toUpperCase();

  Serial.println("Badge détecté : " + uidStr);

  udp.beginPacket(computer_ip, udp_port);

  if (uidStr == "63 3B 92 AA") {
    udp.print("SfcTv");
    Serial.println("Action : SfcTv");
  } else if (uidStr == "03 A2 9B AA") {
    udp.print("SALLE INFO 1");
    Serial.println("Action : SALLE INFO 1");
  }

  udp.endPacket();
  delay(3000);
  rfid.PICC_HaltA();
}