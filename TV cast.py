import subprocess
import sys
import time
import ctypes
import socket

# ───────── Installation auto ─────────

def ensure(package, import_name=None):
    import importlib
    name = import_name or package
    try:
        importlib.import_module(name)
    except ImportError:
        print(f"Installation de {package}...")
        subprocess.check_call([sys.executable, "-m", "pip", "install", package, "-q"])

ensure("pywinauto")
ensure("uiautomation")
ensure("pywin32", "win32api")

from pywinauto import keyboard
import uiautomation as auto


# ───────── Config ─────────

UDP_IP = "0.0.0.0"
UDP_PORT = 5005

MODE = "clone"

VK_LWIN = 0x5B
VK_K = 0x4B
VK_ESC = 0x1B
KEYEVENTF_KEYUP = 0x0002


# ───────── Fonctions clavier ─────────

def key_down(vk):
    ctypes.windll.user32.keybd_event(vk, 0, 0, 0)

def key_up(vk):
    ctypes.windll.user32.keybd_event(vk, 0, KEYEVENTF_KEYUP, 0)

def press_win_k():
    key_down(VK_LWIN)
    key_down(VK_K)
    key_up(VK_K)
    key_up(VK_LWIN)

def press_escape():
    key_down(VK_ESC)
    key_up(VK_ESC)


# ───────── Connexion Miracast ─────────

def connect_tv(TV_NAME):

    print("\n=== Miracast Auto-Connect ===")
    print(f"Recherche TV : {TV_NAME}")

    print("[1] Ouverture panneau Cast (Win+K)...")
    press_win_k()
    time.sleep(4)

    print("[2] Scan des appareils...")

    found = False
    keyboard.send_keys("{TAB}")

    for i in range(1, 11):
        time.sleep(0.6)

        focused = auto.GetFocusedControl()
        name = focused.Name if focused else ""

        print(f"  Tab {i} -> {name}")

        if TV_NAME.lower() in name.lower():

            print(f"  OK - TV trouvee position {i} : {name}")
            time.sleep(0.3)

            keyboard.send_keys("{ENTER}")

            found = True
            break
        keyboard.send_keys("{DOWN}")

    if not found:

        print(f"  ERREUR - '{TV_NAME}' non trouve.")
        print("Verifier que la TV est en mode Screen Mirroring.")

        press_escape()
        return

    time.sleep(5)
    press_escape()


# ───────── UDP Listener ─────────

def udp_listener():

    sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    sock.bind((UDP_IP, UDP_PORT))

    print(f"[OK] Ecoute UDP sur port {UDP_PORT}")

    while True:

        data, addr = sock.recvfrom(1024)

        tv_name = data.decode().strip()

        print(f"\nMessage recu de {addr} : {tv_name}")

        connect_tv(tv_name)


# ───────── Main ─────────

if __name__ == "__main__":

    try:
        udp_listener()

    except KeyboardInterrupt:
        print("\nArret")
