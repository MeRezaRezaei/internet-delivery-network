#!/bin/bash

# Define the installation directory
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)/bin"
mkdir -p "$DIR"
cd "$DIR"

echo "Detecting OS and Architecture..."
OS="$(uname -s)"
ARCH="$(uname -m)"

if [[ "$OS" == "Linux" ]]; then
    if [[ "$ARCH" == "x86_64" ]]; then
        FILENAME="Xray-linux-64.zip"
    else
        echo "Unsupported architecture: $ARCH"
        exit 1
    fi
elif [[ "$OS" == "Darwin" ]]; then
    FILENAME="Xray-macos-64.zip"
elif [[ "$OS" == *"MINGW"* ]] || [[ "$OS" == *"MSYS"* ]] || [[ "$OS" == *"CYGWIN"* ]]; then
    FILENAME="Xray-windows-64.zip"
else
    echo "Unknown OS: $OS, assuming Windows."
    FILENAME="Xray-windows-64.zip"
fi

URL="https://github.com/XTLS/Xray-core/releases/latest/download/${FILENAME}"

echo "Downloading ${URL}..."
curl -L -o "${FILENAME}" "${URL}"

echo "Extracting Xray..."
unzip -o "${FILENAME}" xray xray.exe geoip.dat geosite.dat > /dev/null 2>&1
chmod +x xray 2>/dev/null || true
rm "${FILENAME}"

echo "Xray downloaded and extracted to $DIR"
