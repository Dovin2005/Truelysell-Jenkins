@extends('provider.provider')

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">

        {{-- Page Header --}}
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Google Calendar</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/provider/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Calendar Sync</li>
                    </ul>
                </div>
            </div>
        </div>
               
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="card-title">Sync Google Calendar</h5>
            </div>
            <div class="card-body">
                <p id="syncDescription">
                    @auth
                        @if(Auth::user()->calendar_sync_status == 1)
                            Your calendar is currently synced with Google Calendar.
                        @else
                            Click the button below to Sync your Google Calendar with this application to access your Bookings Google Calendar.
                        @endif
                    @endauth
                </p>
                
                <button id="syncCalendarBtns" class="btn @auth {{ Auth::user()->calendar_sync_status == 1 ? 'btn-warning' : 'btn-danger' }} @endauth" 
                        data-status="@auth {{ Auth::user()->calendar_sync_status == 1 ? 0 : 1 }} @endauth">
                    @auth
                        @if(Auth::user()->calendar_sync_status == 1)
                            <i class="fas fa-unlink"></i> Disconnect Google Calendar
                        @else
                            <i class="fab fa-google"></i> Authorize Google Calendar
                        @endif
                    @endauth
                    
                </button>
                
                <div id="syncMessage" class="mt-3" style="display: none;"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const syncBtn = document.getElementById('syncCalendarBtns');
    const messageDiv = document.getElementById('syncMessage');
    const syncDescription = document.getElementById('syncDescription');

    syncBtn.addEventListener('click', function() {
        const currentStatus = this.getAttribute('data-status'); // This is what we want to SET
        const isConnecting = currentStatus === '1';
        
        // Show loading state
        syncBtn.disabled = true;
        syncBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + 
                           (isConnecting ? 'Connecting...' : 'Disconnecting...');
        messageDiv.style.display = 'none';

        // Make AJAX call
        fetch('/provider/sync-calendar-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: parseInt(currentStatus)
            })
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error('Server returned non-JSON response');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Update UI based on the new status
                updateUI(currentStatus === '1'); // true if connected, false if disconnected
                
                toastr.success('Calendar Connected Successfully');
            } else {
                throw new Error(data.message || 'Operation failed');
            }
        })
        .catch(error => {
            toastr.error('Calendar Disconnected Successfully');
            
            // Reset button to original state on error
            syncBtn.disabled = false;
            const originalStatus = syncBtn.getAttribute('data-status');
            if (originalStatus === '1') {
                syncBtn.innerHTML = '<i class="fab fa-google"></i> Authorize Google Calendar';
            } else {
                syncBtn.innerHTML = '<i class="fas fa-unlink"></i> Disconnect Google Calendar';
            }
        });
    });

    function updateUI(isConnected) {
        if (isConnected) {
            // Now connected - show Disconnect button
            syncBtn.className = 'btn btn-warning';
            syncBtn.setAttribute('data-status', '0'); // Next click will send 0 (disconnect)
            syncBtn.innerHTML = '<i class="fas fa-unlink"></i> Disconnect Google Calendar';
            syncDescription.textContent = 'Your calendar is currently synced with Google Calendar.';
        } else {
            // Now disconnected - show Connect button
            syncBtn.className = 'btn btn-danger';
            syncBtn.setAttribute('data-status', '1'); // Next click will send 1 (connect)
            syncBtn.innerHTML = '<i class="fab fa-google"></i> Authorize Google Calendar';
            syncDescription.textContent = 'Click the button below to Sync your Google Calendar with this application to access your Bookings Google Calendar.';
        }
        syncBtn.disabled = false;
    }
});
</script>
@endsection