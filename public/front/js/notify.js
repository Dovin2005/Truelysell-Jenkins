 function fetchProviderUnreadMessages() {
        $.ajax({
            type: "GET",
            url: "/provider/get-unread-messages",
            dataType: "json",
            success: function (response) {
                const unread = response.count || 0;

                if (unread > 0) {
                    $("#sidebarProviderChatBadge")
                        .removeClass("d-none")
                        .text(unread);
                } else {
                    $("#sidebarProviderChatBadge")
                        .addClass("d-none")
                        .text('');
                }
            }
        });
    }

    function markProviderMessagesRead() {
        $.ajax({
            type: "POST",
            url: "/provider/mark-messages-read",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            },
            success: function () {
                $("#sidebarProviderChatBadge").addClass("d-none").text('');
            }
        });
    }

    $(document).ready(function () {
        fetchProviderUnreadMessages();
        setInterval(fetchProviderUnreadMessages, 60000);

        if (window.location.pathname === "/provider/chat") {
            markProviderMessagesRead();
        }
    });

        function fetchCustomerMessages() {
        $.ajax({
            type: "GET",
            url: "/user/get-unread-messages",
            dataType: "json",
            success: function (response) {
                const unreadCount = response.count || 0;

                if (unreadCount > 0) {
                    $("#sidebarCustomerChatBadge")
                        .removeClass("d-none")
                        .text(unreadCount);
                } else {
                    $("#sidebarCustomerChatBadge")
                        .addClass("d-none")
                        .text('');
                }
            },
            error: function () {
                console.error("Failed to fetch unread message count");
            }
        });
    }

    function markMessagesAsRead() {
        $.ajax({
            type: "POST",
            url: "/user/mark-messages-read",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            },
            success: function () {
                $("#sidebarCustomerChatBadge").addClass("d-none").text('');
            },
            error: function () {
                console.error("Failed to mark messages as read");
            }
        });
    }

    $(document).ready(function () {
        fetchCustomerMessages();

        setInterval(fetchCustomerMessages, 60000);

        if (window.location.pathname === "/user/chat") {
            markMessagesAsRead();
        }
    });