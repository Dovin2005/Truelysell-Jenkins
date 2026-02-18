var pageValue = $('body').data('page');
if(pageValue=== 'admin.calendar'){
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            dayMaxEventRows: true,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: '/api/bookings',
            moreLinkClick: 'popover',
            eventContent: function (info) {
                return {
                    html: `
                        <div style="background-color: ${info.event.backgroundColor};padding: 5px; border-radius: 4px; color: white;">
                            <b>${info.event.title}</b><br/>
                            <small>${info.event.start.toLocaleString()}</small>
                        </div>
                    `
                };
            },
            eventClick: function (info) {
                // Prevent the default action
                info.jsEvent.preventDefault();
                // Display event details in the modal
                const eventTitle = info.event.title;
                const eventDate = info.event.start.toISOString().split('T')[0];
                const provider= info.event.extendedProps.provider;
                const user= info.event.extendedProps.user;
                const location= info.event.extendedProps.location;
                const amount= info.event.extendedProps.amount;
                const status= info.event.extendedProps.status;
                // Populate modal content
                document.getElementById('modalTitle').innerText = eventTitle ?? '';
                document.getElementById('modalDate').innerText = formatDate(eventDate);
                document.getElementById('user').innerText = user;
                document.getElementById('provider').innerText = provider;
                document.getElementById('location').innerText = location;
                document.getElementById('amount').innerText = amount ?? '';
                document.getElementById('status').innerText = status;
               // document.getElementById('status').style.backgroundColor = info.event.backgroundColor;
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('eventModal'));
                modal.show();
            }
        });
        calendar.render();
    });
}
function formatDate(dateString) {
    const date = new Date(dateString); // Parse the date string
    const day = String(date.getDate()).padStart(2, '0'); // Get day with leading zero
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
    const year = date.getFullYear(); // Get full year
    return `${day}-${month}-${year}`; // Return formatted date
}