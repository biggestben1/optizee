# XPrinter Setup Guide

## How to Connect XPrinter to Your POS System

### Method 1: Browser Print (Easiest - Works with Any Printer)

**This is the default method and requires no configuration.**

1. **No setup needed** - Just use the "Print" button in the POS system
2. When you click print, your browser's print dialog will open
3. Select your XPrinter from the printer list
4. Click Print

**Advantages:**
- Works immediately
- No network configuration needed
- Works with USB, Network, and any printer type

---

### Method 2: Network/Ethernet XPrinter (Recommended for Multiple Terminals)

**For XPrinter models with Ethernet port:**

#### Step 1: Connect Printer to Network
1. Connect your XPrinter to your router using an Ethernet cable
2. Power on the printer
3. Print a test receipt - the IP address is usually printed on it
4. If not, check your router's admin panel (usually 192.168.1.1 or 192.168.0.1)
   - Look for "Connected Devices" or "DHCP Client List"
   - Find your XPrinter (may show as "XPrinter" or MAC address)

#### Step 2: Configure in System
1. Go to **Admin Menu → Printer Settings**
2. Click **"Add Printer"**
3. Select **"Network/Ethernet (IP Address)"**
4. Enter the IP address (e.g., 192.168.1.100)
5. Port: **9100** (default for ESC/POS)
6. Paper Width: Select **58mm** or **80mm** (check your printer model)
7. Click **"Save Printer"**

#### Step 3: Test Connection
- The system will use this printer for network printing
- You can still use browser print as fallback

**Common XPrinter IP Addresses:**
- 192.168.1.100
- 192.168.0.100
- 192.168.1.50

---

### Method 3: USB XPrinter

**For XPrinter connected via USB:**

#### Step 1: Install Drivers
1. Download XPrinter drivers from manufacturer website
2. Install drivers on your computer
3. Connect printer via USB
4. Windows should detect it automatically

#### Step 2: Find USB Port
- **Windows:** Check Device Manager → Ports (COM & LPT)
  - Look for "USB Serial Port" or "XPrinter" - note the COM number (e.g., COM3)
- **Linux:** Check `/dev/ttyUSB0` or `/dev/ttyACM0`

#### Step 3: Configure in System
1. Go to **Admin Menu → Printer Settings**
2. Click **"Add Printer"**
3. Select **"USB"**
4. Enter USB port (e.g., COM3 for Windows, /dev/ttyUSB0 for Linux)
5. Select paper width
6. Click **"Save Printer"**

---

### Method 4: Wi-Fi XPrinter

**For XPrinter models with Wi-Fi:**

1. Configure printer to connect to your Wi-Fi network (check printer manual)
2. Find the printer's IP address (print test receipt or check router)
3. Follow **Method 2** steps above using the Wi-Fi IP address

---

## Printer Configuration in System

### Access Printer Settings:
- **Menu:** Operations → Printer Settings (Admin only)
- **URL:** `/admin/printers`

### Adding a Printer:
1. Click **"Add Printer"**
2. Enter printer name (e.g., "Receipt Printer", "Kitchen Printer")
3. Select connection type
4. Enter connection details (IP for network, COM port for USB)
5. Select paper width (58mm or 80mm)
6. Enable "Auto Cut" if your printer supports it
7. Save

### Paper Width Selection:
- **58mm (2 inches):** Common for small receipt printers
- **80mm (3 inches):** Common for larger receipt printers

Check your XPrinter model specifications to confirm.

---

## Testing Your Printer

### Test Browser Print:
1. Complete a sale in POS
2. Click **"Print Receipt"**
3. Select your XPrinter from browser print dialog
4. Verify receipt prints correctly

### Test Network Print:
1. Configure network printer in settings
2. The system will attempt to send print jobs directly
3. Check printer for output

---

## Troubleshooting

### Printer Not Printing:
1. **Check Connection:**
   - Network: Ping the IP address (`ping 192.168.1.100`)
   - USB: Check if printer appears in Device Manager
   
2. **Check Printer Status:**
   - Ensure printer is powered on
   - Check for paper
   - Check for error lights

3. **Check IP Address:**
   - Print a test receipt from printer
   - Verify IP in router admin panel
   - Try accessing printer web interface (if available)

4. **Port Issues:**
   - Default port 9100 should work for most XPrinters
   - Some models use port 515 (LPR) or 631 (IPP)

### Browser Print Not Working:
- Allow popups for the site
- Check browser print settings
- Try different browser (Chrome recommended)

### Network Print Not Working:
- Ensure printer and computer are on same network
- Check firewall settings (port 9100)
- Verify IP address is correct
- Try browser print as alternative

---

## XPrinter Model Compatibility

Most XPrinter models support:
- **ESC/POS commands** (standard thermal printer protocol)
- **Network printing** via IP address
- **USB printing** via serial port

Common XPrinter Series:
- XP-80C, XP-80H (80mm)
- XP-58, XP-58IIH (58mm)
- XP-N160I, XP-N300I (Network models)

---

## Support

For XPrinter-specific issues:
- Check XPrinter official website
- Refer to printer manual
- Contact XPrinter support

For system printing issues:
- Check printer settings in admin panel
- Verify network connectivity
- Use browser print as fallback method
















