# Generate a self-signed certificate for Nginx on Windows (PowerShell).
# Tries system openssl first, then Git for Windows bundled openssl.
# Run from the project root:  powershell -ExecutionPolicy Bypass -File nginx\gen-certs.ps1

$certDir = Join-Path $PSScriptRoot "certs"
New-Item -ItemType Directory -Force -Path $certDir | Out-Null

# Locate openssl: system PATH first, then Git for Windows
$opensslPaths = @(
    "openssl",
    "C:\Program Files\Git\usr\bin\openssl.exe",
    "C:\Program Files (x86)\Git\usr\bin\openssl.exe"
)
$opensslExe = $null
foreach ($p in $opensslPaths) {
    try {
        $null = & $p version 2>&1
        if ($LASTEXITCODE -eq 0) { $opensslExe = $p; break }
    } catch {}
}

if (-not $opensslExe) {
    Write-Error "openssl not found. Install Git for Windows or add OpenSSL to PATH."
    exit 1
}

& $opensslExe req -x509 -nodes -newkey rsa:2048 `
  -keyout "$certDir\selfsigned.key" `
  -out    "$certDir\selfsigned.crt" `
  -days 365 `
  -subj "/C=VN/ST=HN/L=Hanoi/O=Portfolio/OU=Dev/CN=localhost"

if ($LASTEXITCODE -eq 0) {
    Write-Host "Self-signed certificate created in $certDir" -ForegroundColor Green
} else {
    Write-Error "Failed to create certificate."
    exit 1
}
