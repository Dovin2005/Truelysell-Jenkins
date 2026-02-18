(function () {
    "use strict";

    let offset = "";
    let isLoading = false;
    let last_offset = "";

    let page = $('#chatsidebar').data('current_page') || 1;
    let lastPage = $('#chatsidebar').data('last_page') || 1;
    let searchValue = '';
    let loading = false;

    $(document).on('click', '.userprofile', function () {
        let userId = $(this).data('userid');

        $.ajax({
            url: "/user/chat/mark-read",
            type: "POST",
            data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
                from_user_id: userId
            },
            success: function(res) {
                if(res.status === true){
                    $(`.userprofile[data-userid="${userId}"]`).find('.unread-badge').remove();
                }
            }
        });
    });

   

    function loadChatUsers(reset = false) {
        if (loading) return;
        loading = true;

        $.ajax({
            url: '/provider/chat',
            type: 'GET',
            data: { search: searchValue, page: page, is_ajax: 1 },
            success: function (response) {
                let html = '';
                if (response.users.length > 0) {

                    response.users.forEach(function (user) {
                        html += `
                            <li class="user-list-item">
                                <a href="javascript:void(0);" class="p-2 border rounded d-block mb-2 userprofile"
                                   data-userid="${user.id}"
                                   data-username="${user.name}"
                                   data-avatar="${user.profile_image}">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-lg avatar-online me-2 flex-shrink-0">
                                            <img src="${user.profile_image}" alt="Profile Image" class="img-fluid rounded-circle">
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden me-2">
                                            <h6 class="mb-1 text-truncate">${user.name}</h6>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        `;
                    });
                } else if (reset) {
                    html = `<li><p class="text-muted text-center mb-0">${$("#chatsidebar").data("empty_info")}</p></li>`;
                }

                if (reset) {
                    $('.user-list').html(html);
                } else {
                    $('.user-list').append(html);
                }

                page = response.current_page;
                lastPage = response.last_page;
                loading = false;
            },
            error: function () {
                loading = false;
            }
        });
    }

    // Search as you type (reset to page 1)
    $('#chatSearch').on('keyup', function () {
        searchValue = $(this).val();
        page = 1;
        loadChatUsers(true);
    });

    // Infinite scroll inside chat body
    $('.chat-body').on('scroll', function () {
        let $this = $(this);
        if ($this.scrollTop() + $this.innerHeight() >= this.scrollHeight - 10) {
            if (page < lastPage) {
                page++;
                loadChatUsers();
            }
        }
    });

    async function fetchMessages(userId, initial = true, reset = false) {
        if (isLoading || offset === null) return;
        isLoading = true;
        if (reset) {
            offset = "";
            last_offset = "";
        }
        $.ajax({
            url: '/provider/fetch-messages',
            type: 'POST',
            data: {
                'user_id': userId,
                'offset': offset,
                'reset': reset,
                'last_offset': last_offset,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                if (initial) {
                    $("#messageArea").html('');
                }
            },
            success: function (response) {
                if (response.code === 200 && response.messages.length > 0) {
                    let messageArea = $("#messageArea");
                    let messageContainer = $(".chats_scroll");
                    let existingMessages = new Set();

                    $(".message-card").each(function () {
                        existingMessages.add($(this).data("message-id"));
                    });

                    let newMessages = response.messages.filter(msg => !existingMessages.has(msg.id));
                    let html = newMessages.map(message => createMessageCard(message)).join('');

                    if (initial) {
                        messageArea.html(html);
                        setTimeout(() => {
                            messageContainer.scrollTop(messageContainer[0].scrollHeight);
                        }, 10);
                    } else {
                        let oldScrollHeight = messageContainer[0].scrollHeight;
                        messageArea.prepend(html);
                        setTimeout(() => {
                            let newScrollHeight = messageContainer[0].scrollHeight;
                            messageContainer.scrollTop(newScrollHeight - oldScrollHeight);
                        }, 50);
                    }

                    offset = response.next_offset;
                    last_offset = response.last_offset;
                }

                if (offset === null) {
                    $("#messagebody").off("scroll");
                }

                isLoading = false;
            }
        });
    }

    $(".chats_scroll").on("scroll", function () {
        if ($(this).scrollTop() === 0 && !isLoading) {
            let userId = $("#chat_avatar").data('userid');
            fetchMessages(userId, false);
        }
    });

    (async () => {
        let adminId = $("#messageinput").data('receiverid');
        listenMqttForNewMessagesFromAllCustomers(adminId);
        let firstUser = $(".user-list-item .userprofile").first();
        firstUser.trigger('click');
    })();

    function listenMqttForNewMessagesFromAllCustomers() {
        if (typeof mqtt === 'undefined') {
            toastr.error('MQTT not connected!, Please refresh the page');
            return;
        }
        const client = mqtt.connect('wss://broker.emqx.io:8084/mqtt', {
            clientId: 'client_' + Math.random().toString(16).substr(2, 8),
            clean: true,
            reconnectPeriod: 1000,
            connectTimeout: 5000,
        });
        const topic = 'truelysell/to_user/' + $("#sendmsg").data('senderid');
        client.on('connect', function () {
            client.subscribe(topic, { qos: 1 });
        });

        client.on('message', function (receivedTopic, message) {
            const payload = message.toString();
            const messageData = JSON.parse(payload);
            const sender_id = messageData.sender_id;

            let activeUser = $("#chat_avatar").attr('data-userid');
            if (activeUser != sender_id) {
                let user = $(".user-list-item .userprofile[data-userid='" + sender_id + "']");
                user.trigger('click');
            }
            offset = "";
            fetchMessages(sender_id, true);
        });
    }

    $(document).ready(function () {
        let chatUserId = $('#chatsidebar').data('userid');
        let matchedUser = $(`.user-list-item .userprofile[data-userid="${chatUserId}"]`);

        if (matchedUser.length) {
            // Match found
            $("#chat_avatar").attr('data-userid', chatUserId);
            $("#chat_avatar").attr('src', matchedUser.data('avatar'));
            $("#chat_avatar").attr('alt', chatUserId);
            $(".chat-user-name").text(matchedUser.data('username'));
            fetchMessages(chatUserId);
        } else {
            // No match found, optionally fallback to first user
            let firstUser = $(".user-list-item .userprofile").first();
            if (firstUser.length) {
                let user_id = firstUser.data('userid');
                $("#chat_avatar").attr('data-userid', user_id);
                $("#chat_avatar").attr('src', firstUser.data('avatar'));
                $("#chat_avatar").attr('alt', user_id);
                $(".chat-user-name").text(firstUser.data('username'));
                fetchMessages(user_id);
            }
        }
    });

    $(document).on('click', '.userprofile', function () {
        let user_id = $(this).data('userid');
        let avatar = $(this).data('avatar');
        let username = $(this).data('username');
        $("#chat_avatar").attr('data-userid', user_id);
        $("#chat_avatar").attr('src', avatar);
        $("#chat_avatar").attr('alt', user_id);
        $(".chat-user-name").text(username);
        offset = "";
        last_offset = "";
        fetchMessages(user_id, true);
    });

    $(document).on('keydown', '#messageinput', function (e) {
        if (e.keyCode === 13) {
            $("#sendmsg").trigger('click');
        }
    });

    $(document).on('click', '#sendmsg', function () {
        const senderId = $(this).data('senderid');
        const receiverId = $("#chat_avatar").attr('data-userid');
        const message = $("#messageinput").val().trim();
        const topic = `truelysell/to_user/${receiverId}`;
        const file = $("#fileupload")[0].files[0];

        if (!message && !file) {
            toastr.error('Please enter a message or select a file.');
            return;
        }

        let formData = new FormData();
        formData.append('message', message);
        formData.append('sender_id', senderId);
        formData.append('receiver_id', receiverId);
        formData.append('topic', topic);
        formData.append('messageType', file ? 'file' : 'text');
        if (file) formData.append('file', file);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        $.ajax({
            url: "/provider/send-message",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#messageinput").val('').prop('disabled', true);
                $("#sendmsg").prop('disabled', true);
            },
            success: function () {
                offset = "";
                last_offset = "";
                fetchMessages(receiverId, true);
            },
            complete: function () {
                $("#sendmsg").prop('disabled', false);
                $("#messageinput").prop('disabled', false);
                $("#fileupload").val('');
                $(".selected_file").text('');
                $(".selected_file").addClass('d-none');
            },
            error: function () {
                offset = "";
                last_offset = "";
                fetchMessages(receiverId, true);
            }
        });
    });

    function createMessageCard(message) {
        let msgbody = "";
        if (message.position === 'right') {
            msgbody = `<div class="message flex-column align-items-start outgoing">
                            ${message.message_type == 'text' ? `<p class="mb-1">${message.content}</p>` : `<a href="${message.file_path}" target="_blank"><i class="fa fa-link"></i>${message.content}</a>`}
                            <small class="text-muted">${message.created_at_humantime}</small>
                        </div>`;
        } else {
            msgbody = `<div class="message flex-column align-items-start incoming">
                            ${message.message_type == 'text' ? `<p class="mb-1">${message.content}</p>` : `<a href="${message.file_path}" target="_blank"><i class="fa fa-link"></i>${message.content}</a>`}
                            <small class="text-muted">${message.created_at_humantime}</small>
                        </div>`;
        }
        return msgbody;
    }

    $(document).on('click', '#openFile', function () {
        $("#fileupload").trigger('click');
    });

    $(document).on('change', '#fileupload', function () {
        if (this.files.length == 0) {
            $(".selected_file").text('');
            $(".selected_file").addClass('d-none');
            return;
        }
        let fileName = this.files[0].name;
        if (fileName.length > 20) {
            fileName = fileName.substring(0, 20) + '...';
        }
        $(".selected_file").removeClass('d-none');
        $(".selected_file").text(fileName);
    });

})();
