# Tech Evolution & Modern Standards (2026)

## Overview
This document tracks the shift from legacy V2Ray/Xray-core (v1.8.x and below) to the modern **Project X Xray-core (v24-v26 series)**. All future development and AI agents MUST adhere to these updated standards.

---

## 1. Xray-core v26 "Pro" Features (Verified on srv07/srv09)

### A. Core Details
- **Current Version**: Xray v26.3.27 (srv07) / v26.2.6 (srv09).
- **Flagship Transport**: **XHTTP** is widely used in `self-net.json` and other configs, replacing h2/gRPC.
- **Camouflage**: **Finalmask** is supported (v26) and should be used for critical tunnels.
- **Security**: Legacy `allowInsecure` is avoided; configurations use modern TLS settings.

### B. Implementation Patterns
- **Simplified Reverse Proxy**: Inbounds/Outbounds use the `"reverse": { "tag": "..." }` syntax directly.
- **Multi-Node Bridging**: Server 09 (US) acts as the bridge for multiple Iranian portals (01, 04, 07).
- **Path-Based Routing**: Xray configs use distinct paths (e.g., `/c-01-01-01-01`, `/self_net_portal`) for multiplexing tunnels over the same port.

---

## 2. Orchestration Triad: Marzban + Xray + HAProxy

### A. Marzban (User & Node Management)
- **Role**: High-level panel for user management and traffic accounting.
- **Deployment**: Verified on **Server 09** (US) running in Docker containers (`marzban-node`, `marzban-iran-node`).
- **Management API**: 
    - `marzban-node`: Management Port `63050` (Internal), Xray API `63053`.
    - `marzban-iran-node`: Management Port `60050` (Internal), Xray API `60051`.
- **Function**: Marzban injects users into Xray via gRPC/REST without service restarts.

### B. HAProxy (Edge Routing & Optimization)
- **Deployment**: Both srv07 and srv09 use HAProxy (srv09 uses `haproxy:lts-alpine` in Docker).
- **Critical Optimizations**:
    - `option splice-auto`: Enables kernel-level data forwarding for zero-copy performance.
    - `no option http-buffer-request`: Reduces latency for real-time traffic.
    - `tune.h2.initial-window-size`: Set to max (2147483647) for high-bandwidth tunnels.
- **Traffic Routing**:
    - **SNI Routing**: Used on srv07 to route domains like `i-07.doctel.ir` to internal Xray ports.
    - **Path Routing**: Used on srv09 to route paths like `/sv/us` or `/dns-query` (DoH) to specific backends.

### C. DoH / DNS Integration
- **DoH**: Server 09 acts as a DoH gateway, forwarding `/dns-query` to `1.1.1.1` via HAProxy.
- **DoT**: Server 09 exposes a dedicated DoT listener on port `853`.

---

## 3. Implementation Checklist for Agents
1. **Always verify Xray Version**: Ensure `v26.x` is present.
2. **Standard Stack**: Xray for the engine, Marzban for users, HAProxy for routing.
3. **Optimization**: Ensure `splice` and `no-buffer` are enabled in HAProxy backends for all IDN tunnels.
4. **API First**: Use Marzban's API or Xray's gRPC API for user changes.
5. **Path Masking**: Every tunnel must use a unique, non-obvious XHTTP path.
