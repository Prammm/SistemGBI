@extends('layouts.app')

@section('title', 'Kalender Kegiatan')

@section('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
<style>
    #calendar {
        max-width: 1200px;
        margin: 0 auto;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }
    
    /* Custom Calendar Styling */
    .fc {
        border: 1px solid #e3e6f0;
        border-radius: 12px;
    }
    
    .fc-header-toolbar {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 15px 20px;
        border-bottom: 2px solid #e3e6f0;
        margin-bottom: 0 !important;
    }
    
    .fc-toolbar-title {
        color: white !important;
        font-size: 1.5rem !important;
        font-weight: 600 !important;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    
    .fc-button-primary {
        background: rgba(255, 255, 255, 0.2) !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
        color: white !important;
        border-radius: 8px !important;
        font-weight: 500 !important;
        transition: all 0.3s ease !important;
        backdrop-filter: blur(10px);
    }
    
    .fc-button-primary:hover {
        background: rgba(255, 255, 255, 0.3) !important;
        border-color: rgba(255, 255, 255, 0.5) !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .fc-button-primary:focus {
        box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25) !important;
    }
    
    .fc-button-active {
        background: rgba(255, 255, 255, 0.4) !important;
        border-color: rgba(255, 255, 255, 0.6) !important;
    }
    
    /* Day Grid Styling */
    .fc-daygrid-day {
        border: 1px solid #f1f3f4;
        transition: background-color 0.2s ease;
    }
    
    .fc-daygrid-day:hover {
        background-color: #f8f9ff;
    }
    
    .fc-daygrid-day-number {
        font-weight: 500;
        padding: 8px;
        color: #495057;
    }
    
    .fc-day-today {
        background-color: #fff3cd !important;
        border: 2px solid #ffc107 !important;
    }
    
    .fc-day-today .fc-daygrid-day-number {
        color: #856404;
        font-weight: 700;
    }
    
    /* Event Styling */
    .fc-event {
        cursor: pointer;
        border-radius: 6px !important;
        border: none !important;
        padding: 2px 6px;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .fc-event:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    
    .fc-event-title {
        font-weight: 600;
    }
    
    /* Week/Time Grid Styling */
    .fc-timegrid-col-frame {
        border: 1px solid #e9ecef;
    }
    
    .fc-timegrid-slot {
        border-top: 1px solid #f1f3f4;
    }
    
    .fc-timegrid-slot-minor {
        border-top-style: dotted;
    }
    
    /* Legend Styling */
    .event-legend {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 25px;
        padding: 20px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .event-legend-item {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        background: white;
        border-radius: 20px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        transition: transform 0.2s ease;
    }
    
    .event-legend-item:hover {
        transform: translateY(-2px);
    }
    
    .event-legend-color {
        width: 16px;
        height: 16px;
        margin-right: 8px;
        border-radius: 50%;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    
    .event-legend-text {
        font-weight: 500;
        color: #495057;
        font-size: 0.9rem;
    }
    
    /* Custom Date Picker */
    .date-picker-container {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 20px;
        padding: 15px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: 1px solid #e3e6f0;
    }
    
    .date-picker-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 0;
    }
    
    .date-picker-input {
        border: 2px solid #e3e6f0;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }
    
    .date-picker-input:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .go-to-date-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 500;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .go-to-date-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
    }
    
    /* Tooltip Styling */
    .custom-tooltip {
        position: absolute;
        z-index: 1070;
        display: block;
        font-size: 0.875rem;
        opacity: 0;
        background: white;
        border: none;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        padding: 15px;
        max-width: 300px;
        text-align: left;
        transition: opacity 0.3s ease, transform 0.3s ease;
        transform: translateY(10px);
        backdrop-filter: blur(10px);
    }
    
    .custom-tooltip.show {
        opacity: 1;
        transform: translateY(0);
    }
    
    .tooltip-title {
        font-weight: 700;
        margin-bottom: 8px;
        border-bottom: 2px solid #e9ecef;
        padding-bottom: 8px;
        color: #495057;
        font-size: 1rem;
    }
    
    .tooltip-content {
        margin-bottom: 0;
        line-height: 1.5;
        color: #6c757d;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .event-legend {
            flex-direction: column;
            gap: 10px;
        }
        
        .date-picker-container {
            flex-direction: column;
            align-items: stretch;
        }
        
        .fc-toolbar {
            flex-direction: column;
            gap: 10px;
        }
        
        .fc-toolbar-chunk {
            display: flex;
            justify-content: center;
        }
    }
    
    /* Loading Animation */
    .calendar-loading {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 400px;
        font-size: 1.1rem;
        color: #6c757d;
    }
    
    .spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 15px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">
        Kalender Kegiatan
    </h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('kegiatan.index') }}">Kegiatan</a></li>
        <li class="breadcrumb-item active">Kalender</li>
    </ol>
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="card mb-4" style="border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-radius: 16px;">
        <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px 16px 0 0; border: none;">
            <h5 class="mb-0" style="color: white; font-weight: 600;">
                <i class="fas fa-calendar me-2"></i>
                Kalender Kegiatan Gereja
            </h5>
        </div>
        <div class="card-body p-4">
            <!-- Quick Date Navigation -->
            <div class="date-picker-container">
                <label class="date-picker-label">
                    <i class="fas fa-search-location me-2"></i>
                    Pergi ke Tanggal:
                </label>
                <input type="date" id="datePicker" class="date-picker-input">
                <button type="button" class="go-to-date-btn" onclick="goToDate()">
                    <i class="fas fa-arrow-right me-1"></i>
                    Pergi
                </button>
                <button type="button" class="go-to-date-btn" onclick="goToToday()" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                    <i class="fas fa-home me-1"></i>
                    Hari Ini
                </button>
            </div>
            
            <!-- Event Legend -->
            <div class="event-legend">
                <div class="event-legend-item">
                    <div class="event-legend-color" style="background-color: #2ecc71;"></div>
                    <div class="event-legend-text">Ibadah</div>
                </div>
                <div class="event-legend-item">
                    <div class="event-legend-color" style="background-color: #f39c12;"></div>
                    <div class="event-legend-text">Komsel</div>
                </div>
                <div class="event-legend-item">
                    <div class="event-legend-color" style="background-color: #9b59b6;"></div>
                    <div class="event-legend-text">Pelayanan</div>
                </div>
            </div>
            
            <!-- Loading State -->
            <div id="calendarLoading" class="calendar-loading" style="display: none;">
                <div class="spinner"></div>
                Memuat kalender...
            </div>
            
            <!-- Calendar Container -->
            <div id="calendar"></div>
            
            <!-- Custom Tooltip -->
            <div id="event-tooltip" class="custom-tooltip">
                <div class="tooltip-title"></div>
                <div class="tooltip-content"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales/id.js"></script>
<script>
    let calendar;
    
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        const loadingEl = document.getElementById('calendarLoading');
        
        // Show loading
        loadingEl.style.display = 'flex';
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            locale: 'id',
            buttonText: {
                today: 'Hari Ini',
                month: 'Bulan',
                week: 'Minggu',
                day: 'Hari',
                list: 'Daftar'
            },
            events: {!! json_encode($events) !!},
            
            // Prevent button text duplication
            buttonIcons: {
                prev: 'chevron-left',
                next: 'chevron-right'
            },
            
            eventClick: function(info) {
                if (info.event.url) {
                    // Open in new tab with better UX
                    const newWindow = window.open(info.event.url, '_blank');
                    if (newWindow) {
                        newWindow.focus();
                    }
                    return false;
                }
            },
            
            eventMouseEnter: function(info) {
                const tooltip = document.getElementById('event-tooltip');
                const titleEl = tooltip.querySelector('.tooltip-title');
                const contentEl = tooltip.querySelector('.tooltip-content');
                
                titleEl.innerHTML = `<i class="fas fa-calendar-check me-2"></i>${info.event.title}`;
                
                let tooltipContent = '';
                if (info.event.extendedProps.description) {
                    tooltipContent += `<div class="mb-2"><i class="fas fa-info-circle me-2"></i>${info.event.extendedProps.description}</div>`;
                }
                if (info.event.extendedProps.location) {
                    tooltipContent += `<div class="mb-2"><i class="fas fa-map-marker-alt me-2"></i><strong>Lokasi:</strong> ${info.event.extendedProps.location}</div>`;
                }
                
                // Add time information
                if (info.event.start) {
                    const startTime = info.event.start.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    const endTime = info.event.end ? info.event.end.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    }) : '';
                    
                    tooltipContent += `<div><i class="fas fa-clock me-2"></i><strong>Waktu:</strong> ${startTime}${endTime ? ' - ' + endTime : ''}</div>`;
                }
                
                contentEl.innerHTML = tooltipContent;
                
                // Position tooltip with better logic
                const rect = info.el.getBoundingClientRect();
                const tooltipRect = tooltip.getBoundingClientRect();
                
                let top = rect.top + window.scrollY - tooltip.offsetHeight - 10;
                let left = rect.left + window.scrollX + (rect.width / 2) - (tooltip.offsetWidth / 2);
                
                // Adjust if tooltip goes outside viewport
                if (left < 10) left = 10;
                if (left + tooltip.offsetWidth > window.innerWidth - 10) {
                    left = window.innerWidth - tooltip.offsetWidth - 10;
                }
                
                if (top < window.scrollY + 10) {
                    top = rect.bottom + window.scrollY + 10;
                }
                
                tooltip.style.top = top + 'px';
                tooltip.style.left = left + 'px';
                tooltip.classList.add('show');
            },
            
            eventMouseLeave: function() {
                const tooltip = document.getElementById('event-tooltip');
                tooltip.classList.remove('show');
            },
            
            windowResize: function(view) {
                // Hide tooltip on window resize
                const tooltip = document.getElementById('event-tooltip');
                tooltip.classList.remove('show');
            },
            
            loading: function(bool) {
                if (bool) {
                    loadingEl.style.display = 'flex';
                    calendarEl.style.display = 'none';
                } else {
                    loadingEl.style.display = 'none';
                    calendarEl.style.display = 'block';
                }
            },
            
            // Enhanced styling options
            themeSystem: 'bootstrap5',
            firstDay: 0, // Sunday as first day
            timeZone: 'local',
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false,
                hour12: false
            },
            views: {
                timeGrid: {
                    dayMaxEvents: 4,
                    dayMaxEventRows: 4
                }
            },
            dayMaxEvents: true,
            navLinks: true,
            businessHours: {
                daysOfWeek: [0, 1, 2, 3, 4, 5, 6],
                startTime: '08:00', 
                endTime: '20:00'
            },
            nowIndicator: true,
            weekNumbers: false,
            weekNumberCalculation: 'ISO',
            editable: false,
            selectable: false,
            unselectAuto: true,
            selectMirror: true,
            eventResizableFromStart: false,
            
            // Prevent button text duplication issue
            customButtons: {},
            
            // Add event content styling
            eventContent: function(arg) {
                return {
                    html: `<div style="padding: 2px 4px; font-size: 0.85rem;">
                            <div style="font-weight: 600;">${arg.event.title}</div>
                           </div>`
                };
            }
        });
        
        calendar.render();
        
        // Set today's date as default in date picker
        const today = new Date();
        const dateString = today.toISOString().split('T')[0];
        document.getElementById('datePicker').value = dateString;
        
        // Hide loading after calendar renders
        setTimeout(() => {
            loadingEl.style.display = 'none';
        }, 500);
    });
    
    // Function to go to specific date
    function goToDate() {
        const datePicker = document.getElementById('datePicker');
        const selectedDate = datePicker.value;
        
        if (selectedDate) {
            calendar.gotoDate(selectedDate);
            
            // Add visual feedback
            const btn = document.querySelector('.go-to-date-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check me-1"></i>Berhasil!';
            btn.style.background = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
            }, 1500);
        } else {
            alert('Silakan pilih tanggal terlebih dahulu');
        }
    }
    
    // Function to go to today
    function goToToday() {
        calendar.today();
        
        // Update date picker to today
        const today = new Date();
        const dateString = today.toISOString().split('T')[0];
        document.getElementById('datePicker').value = dateString;
        
        // Add visual feedback
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check me-1"></i>Hari Ini!';
        
        setTimeout(() => {
            btn.innerHTML = originalText;
        }, 1500);
    }
    
    // Add keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            switch(e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    calendar.prev();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    calendar.next();
                    break;
                case 'Home':
                    e.preventDefault();
                    calendar.today();
                    break;
            }
        }
    });
    
    // Handle window resize for responsive tooltip
    window.addEventListener('resize', function() {
        const tooltip = document.getElementById('event-tooltip');
        tooltip.classList.remove('show');
    });
</script>
@endsection</content>