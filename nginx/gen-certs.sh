#!/usr/bin/env bash
# Generate a self-signed certificate for Nginx (365 days).
# Run from the project root:  bash nginx/gen-certs.sh
set -e
CERT_DIR="$(dirname "$0")/certs"
mkdir -p "$CERT_DIR"

openssl req -x509 -nodes -newkey rsa:2048 \
  -keyout "$CERT_DIR/selfsigned.key" \
  -out    "$CERT_DIR/selfsigned.crt" \
  -days 365 \
  -subj "/C=VN/ST=HN/L=Hanoi/O=Portfolio/OU=Dev/CN=localhost"

echo "Self-signed certificate created in $CERT_DIR"
