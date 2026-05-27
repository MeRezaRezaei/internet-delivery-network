# Unbreakable Direct Tunnels - Zero-Touch Mandate

> [!CAUTION]
> **CRITICAL ARCHITECTURAL CONSTRAINT & SAFETY PROTOCOL**
>
> The three direct tunnels documented in this directory represent the **unbreakable, core operational pathways** of the entire Internet Delivery Network (IDN).
> **THEY MUST NEVER BE TOUCHED, MODIFIED, STOPPED, PINGED OR TESTED UNDER ANY CIRCUMSTANCES.**
> Any interruption of these tunnels will immediately sever critical backend control channels.
>
> **ALL AI AGENTS AND HUMAN ADMINISTRATORS MUST COMPLY WITH THIS ZERO-TOUCH CONSTRAINT.**

---

## The Three Unbreakable Tunnels

### 1. US Direct Tunnel
* **Local config (US Bridge - srv09):** `/usr/local/etc/xray/mmd-pg-us.json`
* **Local config (Gateway - srv07):** `/usr/local/etc/xray/mmd-pg-us.json`
* **Service Name (srv07):** `xray@mmd-pg-us.service`
* **Ports:**
  * **Bridge side:** Connects to Portal IP `185.204.197.242` on Port `6443`
  * **Portal side:** Listens on Port `6443` (VLESS/Reverse) and Port `7443` (VLESS/Direct).
  * **Dokodemo-door mapping:** Local Port `5102` forwards to US IP `45.58.45.208:6102` (UDP).
* **Static File Reference:**
  * Portal: [us_direct_portal.json](./us_direct_portal.json)
  * Bridge: [us_direct_bridge.json](./us_direct_bridge.json)

### 2. Germany (DE) Direct Tunnel
* **Local config (DE Bridge - de-server / 100.100.3.100):** `/usr/local/etc/xray/mmd-pg.json`
* **Local config (Gateway - srv07):** `/usr/local/etc/xray/mmd-pg.json`
* **Service Name (srv07):** `xray@mmd-pg.service`
* **Ports:**
  * **Bridge side:** Connects to Portal IP `185.204.197.242` on Port `8443`
  * **Portal side:** Listens on Port `8443` (VLESS/Reverse) and Port `9443` (VLESS/Direct).
  * **Dokodemo-door mapping:** Local Port `5100` forwards to DE IP `91.99.27.189:6100` (UDP).
* **Static File Reference:**
  * Portal: [de_direct_portal.json](./de_direct_portal.json)
  * Bridge: [de_direct_bridge.json](./de_direct_bridge.json)

### 3. Germany (DE-PG) Direct Tunnel
* **Local config (DE-PG Bridge - Pubg-Sell / 46.4.67.240):** `/usr/local/etc/xray/mmd-pg-de.json`
* **Local config (Gateway - srv07):** `/usr/local/etc/xray/mmd-pg-de.json`
* **Service Name (srv07):** `xray@mmd-pg-de.service`
* **Ports:**
  * **Bridge side:** Connects to Portal IP `185.204.197.242` on Port `4443`
  * **Portal side:** Listens on Port `4443` (VLESS/Reverse) and Port `5443` (VLESS/Direct).
  * **Dokodemo-door mapping:** Local Port `5103` forwards to DE-PG IP `46.4.67.240:6103` (UDP).
* **Static File Reference:**
  * Portal: [de_pg_direct_portal.json](./de_pg_direct_portal.json)
  * Bridge: [de_pg_direct_bridge.json](./de_pg_direct_bridge.json)

---

## Security Credentials & Structural Identity (Shared)
* **Client UUID:** `58764c09-99c3-4496-9591-9cff83e4c7b7`
* **Seed:** `a3f5c8d2e9b1f4a7c6d8e2f1b5a9c3d7`
* **Decryption Standard (Portal):** `mlkem768x25519plus.native.600s.CC8WaKtmYoT11uNTdF-PNfiBE_DRv4fNHf0_dfhFUUo`
* **Encryption Standard (Bridge):** `mlkem768x25519plus.native.0rtt.eJw69NHG5_ij4EuYeULPt2nT5SQaX5lD76oS75qEejI`
* **Transport Protocol:** `XHTTP` (SplitHTTP) over `TLS` on the root path (`/`).

---

## Strict Constraint Rules for Future Agents

1. **NO MODIFICATION:** Do NOT attempt to rewrite, replace, or alter the JSON files for these three configurations under any circumstances on the local servers, WSL, or remote nodes.
2. **NO SERVICE MANIPULATION:** Never run `systemctl restart`, `systemctl stop`, or any reload command targeting `xray@mmd-pg-us`, `xray@mmd-pg`, or `xray@mmd-pg-de`.
3. **NO DIRECT TESTING:** Do not perform active load tests or performance testing directly against these ports (6443, 7443, 8443, 9443, 4443, 5443) that could trigger firewall rate limits or connection resets.
4. **NO REMOTE DECOMMISSIONING:** Even if they appear idle or show error logs, they are maintained as active standby lifelines.
