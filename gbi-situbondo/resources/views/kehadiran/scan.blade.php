@extends('layouts.app')

@section('title', 'QR Scanner Presensi')

@section('styles')
<style>
    .scanner-container {
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .camera-container {
        position: relative;
        background: #000;
        border-radius: 15px;
        overflow: hidden;
        margin-bottom: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }
    
    #scanner-video {
        width: 100%;
        height: auto;
        display: block;
    }
    
    .scanner-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 250px;
        height: 250px;
        border: 3px solid #fff;
        border-radius: 15px;
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(5px);
    }
    
    .scanner-corners {
        position: absolute;
        width: 250px;
        height: 250px;
    }
    
    .scanner-corner {
        position: absolute;
        width: 25px;
        height: 25px;
        border: 3px solid #4CAF50;
    }
    
    .scanner-corner.top-left {
        top: -3px;
        left: -3px;
        border-right: none;
        border-bottom: none;
        border-top-left-radius: 15px;
    }
    
    .scanner-corner.top-right {
        top: -3px;
        right: -3px;
        border-left: none;
        border-bottom: none;
        border-top-right-radius: 15px;
    }
    
    .scanner-corner.bottom-left {
        bottom: -3px;
        left: -3px;
        border-right: none;
        border-top: none;
        border-bottom-left-radius: 15px;
    }
    
    .scanner-corner.bottom-right {
        bottom: -3px;
        right: -3px;
        border-left: none;
        border-top: none;
        border-bottom-right-radius: 15px;
    }
    
    .scanner-line {
        position: absolute;
        left: 0;
        width: 100%;
        height: 2px;
        background: linear-gradient(90deg, transparent, #4CAF50, transparent);
        animation: scanner-line 2s linear infinite;
    }
    
    @keyframes scanner-line {
        0% { top: 0; }
        100% { top: 100%; }
    }
    
    .scanner-controls {
        text-align: center;
        margin-bottom: 20px;
    }
    
    .scanner-status {
        text-align: center;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 15px;
        font-weight: 500;
    }
    
    .scanner-status.waiting {
        background: #e3f2fd;
        color: #1976d2;
        border: 1px solid #bbdefb;
    }
    
    .scanner-status.scanning {
        background: #fff3e0;
        color: #f57c00;
        border: 1px solid #ffcc02;
    }
    
    .scanner-status.success {
        background: #e8f5e8;
        color: #2e7d32;
        border: 1px solid #4caf50;
    }
    
    .scanner-status.error {
        background: #ffebee;
        color: #c62828;
        border: 1px solid #f44336;
    }
    
    .qr-code-fallback {
        text-align: center;
        padding: 20px;
        background: #f5f5f5;
        border-radius: 10px;
        margin-top: 20px;
    }
    
    .camera-selector {
        margin-bottom: 15px;
    }
    
    .camera-permission-denied {
        text-align: center;
        padding: 30px;
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 10px;
        color: #856404;
    }
    
    .torch-button {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(0,0,0,0.7);
        border: none;
        color: white;
        padding: 10px;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .torch-button:hover {
        background: rgba(0,0,0,0.9);
        transform: scale(1.1);
    }
    
    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 10px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .event-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 15px;
        margin-bottom: 20px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .manual-input-card {
        background: #f8f9fa;
        border: 1px dashed #dee2e6;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        margin-top: 20px;
    }

    .security-notice {
        background: #fff3cd;
        border: 1px solid #ffc107;
        border-radius: 10px;
        padding: 15px;
        margin-top: 20px;
        color: #856404;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">QR Scanner Presensi</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kehadiran.index') }}">Presensi Kehadiran</a></li>
        <li class="breadcrumb-item active">QR Scanner</li>
    </ol>
    
    <div class="scanner-container">
        <div class="event-info">
            <h4><i class="fas fa-calendar-check me-2"></i>{{ $pelaksanaan->kegiatan->nama_kegiatan }}</h4>
            <div class="row mt-3">
                <div class="col-md-6">
                    <div><i class="fas fa-calendar me-2"></i>{{ \Carbon\Carbon::parse($pelaksanaan->tanggal_kegiatan)->format('d F Y') }}</div>
                    <div><i class="fas fa-clock me-2"></i>{{ \Carbon\Carbon::parse($pelaksanaan->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($pelaksanaan->jam_selesai)->format('H:i') }}</div>
                </div>
                <div class="col-md-6">
                    <div><i class="fas fa-map-marker-alt me-2"></i>{{ $pelaksanaan->lokasi ?: 'Lokasi belum ditentukan' }}</div>
                </div>
            </div>
        </div>
        
        @php
            $user = Auth::user();
            
            // PERBAIKAN: Gunakan setTimeFromTimeString untuk menghindari double time specification
            try {
                $eventDate = \Carbon\Carbon::parse($pelaksanaan->tanggal_kegiatan);
                $eventStartTime = $eventDate->copy()->setTimeFromTimeString($pelaksanaan->jam_mulai);
                $eventEndTime = $eventDate->copy()->setTimeFromTimeString($pelaksanaan->jam_selesai);
            } catch (\Exception $e) {
                // Fallback jika ada error parsing
                $eventDate = \Carbon\Carbon::parse($pelaksanaan->tanggal_kegiatan);
                $eventStartTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', 
                    $eventDate->format('Y-m-d') . ' ' . substr($pelaksanaan->jam_mulai, 0, 5));
                $eventEndTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', 
                    $eventDate->format('Y-m-d') . ' ' . substr($pelaksanaan->jam_selesai, 0, 5));
            }
            
            $now = \Carbon\Carbon::now();
            
            // QR Code availability window for regular members
            $qrStartTime = $eventStartTime->copy()->subMinutes(30);
            $qrEndTime = $eventEndTime->copy();
            
            $isQrAvailable = $user->id_role <= 3 || $now->between($qrStartTime, $qrEndTime);
        @endphp
        
        @if($user->id_role > 3 && !$isQrAvailable)
            <div class="security-notice">
                <h6><i class="fas fa-shield-alt me-2"></i>Presensi Belum Tersedia</h6>
                <p class="mb-2">Untuk menjaga keamanan presensi, QR Code hanya tersedia:</p>
                <ul class="mb-2">
                    <li><strong>Mulai:</strong> {{ $qrStartTime->format('d/m/Y H:i') }} (30 menit sebelum acara)</li>
                    <li><strong>Berakhir:</strong> {{ $qrEndTime->format('d/m/Y H:i') }} (saat acara selesai)</li>
                </ul>
                @if($now->lt($qrStartTime))
                    <p class="mb-0"><strong>QR Code akan tersedia dalam {{ $now->diffInMinutes($qrStartTime) }} menit lagi.</strong></p>
                @else
                    <p class="mb-0"><strong>Waktu presensi telah berakhir.</strong></p>
                @endif
            </div>
            
            <div class="text-center mt-4">
                <a href="{{ route('kehadiran.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        @else
            <div id="scanner-status" class="scanner-status waiting">
                <i class="fas fa-camera me-2"></i>Siap untuk memulai scanning...
            </div>
            
            <div class="camera-selector" id="camera-selector" style="display: none;">
                <label for="camera-select" class="form-label">Pilih Kamera:</label>
                <select id="camera-select" class="form-select">
                    <option value="">Pilih kamera...</option>
                </select>
            </div>
            
            <div class="scanner-controls">
                <button id="start-scan-btn" class="btn btn-primary btn-lg me-2">
                    <i class="fas fa-camera"></i> Mulai Scanner
                </button>
                <button id="stop-scan-btn" class="btn btn-danger btn-lg" style="display: none;">
                    <i class="fas fa-stop"></i> Stop Scanner
                </button>
            </div>
            
            <div class="camera-container" id="camera-container" style="display: none;">
                <video id="scanner-video" playsinline></video>
                <div class="scanner-overlay">
                    <div class="scanner-corners">
                        <div class="scanner-corner top-left"></div>
                        <div class="scanner-corner top-right"></div>
                        <div class="scanner-corner bottom-left"></div>
                        <div class="scanner-corner bottom-right"></div>
                    </div>
                    <div class="scanner-line"></div>
                </div>
                <button id="torch-btn" class="torch-button" style="display: none;">
                    <i class="fas fa-lightbulb"></i>
                </button>
            </div>
            
            <div id="camera-permission-denied" class="camera-permission-denied" style="display: none;">
                <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                <h5>Akses Kamera Ditolak</h5>
                <p>Untuk menggunakan QR Scanner, Anda perlu memberikan izin akses kamera. Silahkan:</p>
                <ol class="text-start">
                    <li>Klik ikon kamera/kunci di address bar browser</li>
                    <li>Pilih "Allow" atau "Izinkan" untuk akses kamera</li>
                    <li>Refresh halaman ini</li>
                </ol>
                <button onclick="location.reload()" class="btn btn-primary mt-3">
                    <i class="fas fa-refresh"></i> Refresh Halaman
                </button>
            </div>
            
            @if($user->id_role <= 3)
                <!-- QR Code Statis hanya untuk Petugas Pelayanan ke atas -->
                <div class="qr-code-fallback">
                    <h6><i class="fas fa-qrcode me-2"></i>QR Code Statis</h6>
                    <p>Jika kamera tidak berfungsi, gunakan aplikasi QR scanner lain untuk scan kode ini:</p>
                    <div id="qrcode" class="mb-3"></div>
                    <small class="text-muted">Scan dengan aplikasi QR scanner di ponsel Anda</small>
                </div>
            @else
                <!-- Untuk Anggota Jemaat - tidak ada QR Code statis -->
                <div class="manual-input-card">
                    <i class="fas fa-shield-alt fa-3x mb-3 text-muted"></i>
                    <h6>Silahkan Scan QR Code</h6>
                    <p class="text-muted">Silahkan gunakan kamera untuk melakukan scan atau hubungi petugas jika mengalami kesulitan.</p>
                </div>
            @endif
            
            <div class="text-center mt-4">
                <a href="{{ route('kehadiran.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/@zxing/library@latest"></script>
@if($user->id_role <= 3)
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
@endif

@if($isQrAvailable)
<script>
document.addEventListener('DOMContentLoaded', function() {
    let codeReader = new ZXing.BrowserQRCodeReader();
    let scanning = false;
    let selectedDeviceId = null;
    let stream = null;
    let torchSupported = false;
    let torchEnabled = false;

    const video = document.getElementById('scanner-video');
    const cameraContainer = document.getElementById('camera-container');
    const startBtn = document.getElementById('start-scan-btn');
    const stopBtn = document.getElementById('stop-scan-btn');
    const statusDiv = document.getElementById('scanner-status');
    const cameraSelector = document.getElementById('camera-selector');
    const cameraSelect = document.getElementById('camera-select');
    const permissionDenied = document.getElementById('camera-permission-denied');
    const torchBtn = document.getElementById('torch-btn');

    @if($user->id_role <= 3)
    // Initialize static QR code for staff only
    new QRCode(document.getElementById("qrcode"), {
        text: "{{ $qrUrl }}",
        width: 200,
        height: 200,
        colorDark: "#000000",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
    @endif

    // Update status
    function updateStatus(message, type = 'waiting') {
        statusDiv.className = `scanner-status ${type}`;
        statusDiv.innerHTML = message;
    }

    // Get available cameras
    async function getVideoDevices() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const videoDevices = devices.filter(device => device.kind === 'videoinput');
            
            if (videoDevices.length > 1) {
                cameraSelector.style.display = 'block';
                cameraSelect.innerHTML = '<option value="">Pilih kamera...</option>';
                
                videoDevices.forEach((device, index) => {
                    const option = document.createElement('option');
                    option.value = device.deviceId;
                    option.text = device.label || `Kamera ${index + 1}`;
                    cameraSelect.appendChild(option);
                });
                
                // Default to back camera if available
                const backCamera = videoDevices.find(device => 
                    device.label.toLowerCase().includes('back') || 
                    device.label.toLowerCase().includes('rear')
                );
                if (backCamera) {
                    selectedDeviceId = backCamera.deviceId;
                    cameraSelect.value = selectedDeviceId;
                }
            }
        } catch (error) {
            console.error('Error getting video devices:', error);
        }
    }

    // Check torch support
    function checkTorchSupport() {
        if (stream && stream.getVideoTracks().length > 0) {
            const track = stream.getVideoTracks()[0];
            const capabilities = track.getCapabilities();
            
            if (capabilities.torch) {
                torchSupported = true;
                torchBtn.style.display = 'block';
            }
        }
    }

    // Toggle torch
    async function toggleTorch() {
        if (!torchSupported || !stream) return;
        
        try {
            const track = stream.getVideoTracks()[0];
            await track.applyConstraints({
                advanced: [{ torch: !torchEnabled }]
            });
            
            torchEnabled = !torchEnabled;
            torchBtn.innerHTML = torchEnabled ? 
                '<i class="fas fa-lightbulb"></i>' : 
                '<i class="far fa-lightbulb"></i>';
        } catch (error) {
            console.error('Error toggling torch:', error);
        }
    }

    // Start scanning
    async function startScanning() {
        if (scanning) return;
        
        try {
            updateStatus('<div class="loading-spinner"></div>Mengaktifkan kamera...', 'scanning');
            
            const constraints = {
                video: {
                    facingMode: 'environment', // Use back camera by default
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                }
            };
            
            if (selectedDeviceId) {
                constraints.video.deviceId = { exact: selectedDeviceId };
            }
            
            stream = await navigator.mediaDevices.getUserMedia(constraints);
            video.srcObject = stream;
            
            await video.play();
            
            cameraContainer.style.display = 'block';
            startBtn.style.display = 'none';
            stopBtn.style.display = 'inline-block';
            scanning = true;
            
            checkTorchSupport();
            updateStatus('<i class="fas fa-qrcode me-2"></i>Arahkan kamera ke QR Code...', 'scanning');
            
            // Start QR code detection
            codeReader.decodeFromVideoDevice(selectedDeviceId, video, (result, err) => {
                if (result) {
                    handleQRResult(result.text);
                }
                
                if (err && !(err instanceof ZXing.NotFoundException)) {
                    console.error('QR scanning error:', err);
                }
            });
            
        } catch (error) {
            console.error('Error starting camera:', error);
            
            if (error.name === 'NotAllowedError') {
                permissionDenied.style.display = 'block';
                cameraContainer.style.display = 'none';
                updateStatus('<i class="fas fa-exclamation-triangle me-2"></i>Akses kamera ditolak', 'error');
            } else {
                updateStatus('<i class="fas fa-exclamation-triangle me-2"></i>Gagal mengaktifkan kamera: ' + error.message, 'error');
            }
        }
    }

    // Stop scanning
    function stopScanning() {
        scanning = false;
        
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        
        codeReader.reset();
        video.srcObject = null;
        
        cameraContainer.style.display = 'none';
        startBtn.style.display = 'inline-block';
        stopBtn.style.display = 'none';
        torchBtn.style.display = 'none';
        
        updateStatus('<i class="fas fa-camera me-2"></i>Scanner dihentikan', 'waiting');
    }

    // Handle QR scan result
    function handleQRResult(qrText) {
        // Check if the QR contains our URL
        const expectedUrl = "{{ $qrUrl }}";
        
        if (qrText === expectedUrl || qrText.includes('kehadiran/scan-process/{{ $pelaksanaan->id_pelaksanaan }}')) {
            updateStatus('<i class="fas fa-check-circle me-2"></i>QR Code berhasil dipindai! Memproses...', 'success');
            stopScanning();
            
            // Redirect to process attendance
            setTimeout(() => {
                window.location.href = expectedUrl;
            }, 1000);
        } else {
            updateStatus('<i class="fas fa-exclamation-triangle me-2"></i>QR Code tidak valid untuk kegiatan ini', 'error');
            
            // Reset status after 3 seconds
            setTimeout(() => {
                updateStatus('<i class="fas fa-qrcode me-2"></i>Arahkan kamera ke QR Code...', 'scanning');
            }, 3000);
        }
    }

    // Event listeners
    startBtn.addEventListener('click', startScanning);
    stopBtn.addEventListener('click', stopScanning);
    torchBtn.addEventListener('click', toggleTorch);
    
    cameraSelect.addEventListener('change', function() {
        selectedDeviceId = this.value;
        if (scanning) {
            stopScanning();
            setTimeout(startScanning, 500);
        }
    });

    // Initialize
    getVideoDevices();
    
    // Auto-start if user has already granted camera permission
    navigator.permissions.query({ name: 'camera' }).then(function(result) {
        if (result.state === 'granted') {
            // Auto-start scanning
            setTimeout(startScanning, 1000);
        }
    }).catch(() => {
        // Permissions API not supported, user will need to click start
    });
    
    // Handle page visibility change
    document.addEventListener('visibilitychange', function() {
        if (document.hidden && scanning) {
            stopScanning();
        }
    });
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (scanning) {
            stopScanning();
        }
    });
});
</script>
@endif
@endsection