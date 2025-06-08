@extends('emails.layouts.church')

@section('title', 'Kami Merindukan Kehadiran Anda')
@section('header-title', 'Kami Merindukan Anda')
@section('header-subtitle', 'Keluarga Besar GBI Situbondo')

@section('content')
<div class="greeting">Shalom, {{ $anggota_nama }}</div>

<p>Kami menulis surat ini dengan hati yang penuh kasih dan kerinduan untuk Anda.</p>

<div class="alert alert-info">
    Kami menyadari bahwa Anda belum dapat hadir dalam {{ $absence_count }} {{ $tipe_kegiatan === 'komsel' ? 'pertemuan komsel' : 'ibadah' }} terakhir. Kehadiran Anda sangat berharga bagi keluarga besar GBI Situbondo.
</div>

@if(!empty($recent_absences))
<div class="event-details">
    <h3>{{ $tipe_kegiatan === 'komsel' ? 'Pertemuan Komsel' : 'Ibadah' }} yang Terlewatkan</h3>
    @foreach($recent_absences as $absence)
    <div class="detail-item">
        <span class="detail-label">{{ $absence['tanggal'] }}:</span>
        <span class="detail-value">{{ $absence['kegiatan'] }}</span>
    </div>
    @endforeach
</div>
@endif

<p>Kami memahami bahwa mungkin ada situasi atau keadaan yang membuat Anda sulit untuk hadir. Kami ingin Anda tahu bahwa:</p>

<ul>
    <li><strong>Anda tidak sendirian</strong> - Keluarga gereja selalu ada untuk mendukung Anda</li>
    <li><strong>Anda dirindukan</strong> - Persekutuan tidak lengkap tanpa kehadiran Anda</li>
    <li><strong>Kami peduli</strong> - Jika ada yang bisa kami bantu, jangan ragu untuk menghubungi kami</li>
</ul>

<p>Jika ada hal yang ingin Anda bagikan atau jika Anda memerlukan dukungan doa, kami dengan senang hati akan mendengarkan dan melayani Anda.</p>

<div class="contact-info">
    <p><strong>Jangan ragu untuk menghubungi:</strong></p>
    <p>{{ $tipe_kegiatan === 'komsel' ? 'Pemimpin komsel atau ' : '' }}Pengurus gereja di nomor telepon dan email yang tertera di bawah ini.</p>
    
    <p style="margin-top: 20px;"><em>"Sebab itu nasihatilah seorang akan yang lain dan saling membangunlah, seperti yang memang kamu lakukan!" - 1 Tesalonika 5:11</em></p>
</div>
@endsection