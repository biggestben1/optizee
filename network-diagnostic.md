# Network Diagnostic Checklist

## Current Status
- **Server IP:** 192.168.0.116
- **Server Port:** 80
- **Server Status:** Running and listening on 0.0.0.0:80
- **Firewall Rule:** Added (Herd HTTP)

## Most Common Issues (Check in Order)

### 1. Router AP Isolation (MOST LIKELY)
**Problem:** Router prevents devices on same network from talking to each other.

**How to Check:**
- Access router admin: `http://192.168.0.1` or `http://192.168.1.1`
- Look for: "AP Isolation", "Wireless Isolation", "Client Isolation"
- **Action:** DISABLE it

### 2. Phone on Guest Network
**Problem:** Guest networks are isolated from main network.

**How to Check:**
- On phone: Settings → Wi-Fi
- Check if connected to "Guest" or "Guest Network"
- **Action:** Connect to main Wi-Fi network (same as computer)

### 3. Different Wi-Fi Bands
**Problem:** Computer on 5GHz, phone on 2.4GHz (or vice versa) - some routers isolate these.

**How to Check:**
- Computer: Check Wi-Fi adapter properties
- Phone: Check Wi-Fi network name (may show "5G" or "2.4G")
- **Action:** Connect both to same band

### 4. Antivirus Firewall
**Problem:** Antivirus software has its own firewall blocking connections.

**How to Check:**
- Check Windows Security → Firewall & network protection
- Check any third-party antivirus (Norton, McAfee, etc.)
- **Action:** Temporarily disable to test

### 5. Network Subnet Mismatch
**Problem:** Devices on different subnets.

**How to Check:**
- Computer IP: 192.168.0.116 (subnet: 192.168.0.x)
- Phone IP: Should also be 192.168.0.x
- **Action:** Ensure both on same subnet

### 6. Herd Configuration
**Problem:** Herd might not be configured to accept external connections.

**How to Check:**
- Herd settings → Check if "Allow external access" is enabled
- **Action:** Enable external access in Herd settings

## Quick Tests

### Test 1: Ping from Phone
- Install network tool app (Fing, Network Analyzer)
- Ping: 192.168.0.116
- **If fails:** Router isolation issue

### Test 2: Check Phone IP
- Phone: Settings → Wi-Fi → Tap connected network → View IP
- Should be: 192.168.0.x (same subnet as computer)
- **If different:** Network configuration issue

### Test 3: Try Different Device
- Try from another phone/tablet
- **If works:** Phone-specific issue
- **If fails:** Network/router issue

### Test 4: Try Different Port
- Temporarily use port 8000 instead of 80
- Access: `http://192.168.0.116:8000`
- **If works:** Port 80 blocked by something else

## Step-by-Step Solution

1. **First:** Check router AP isolation (most common)
2. **Second:** Verify phone is on main network (not guest)
3. **Third:** Check Wi-Fi bands match
4. **Fourth:** Test with ping from phone
5. **Fifth:** Check antivirus settings
















