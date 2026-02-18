window.searchInJson = function (keyToSearch, jsonData) {
    keyToSearch = keyToSearch.toLowerCase();
    let result = "";

    $.each(jsonData, function (key, value) {
        if (key.toLowerCase().includes(keyToSearch)) {
            result = value;
        }
    });

    if (result) {
        return result;
    }
}

window.loadJsonFile = function (searchKey, callback) {
    const jsonFilePath = "/lang/ar.json";
    $.getJSON(jsonFilePath, function (data) {
        let lang = searchInJson(searchKey, data);
        callback(lang);
    }).fail(function () {
        alert("Failed to load JSON file.");
    });
}

window.datatableLang = {
    lengthMenu: $("#datatable_data").data("length_menu"),
    info: $("#datatable_data").data("info"),
    infoEmpty: $("#datatable_data").data("info_empty"),
    infoFiltered: $("#datatable_data").data("info_filter"),
    search: $("#datatable_data").data("search"),
    zeroRecords: $("#datatable_data").data("zero_records"),
    paginate: {
        first: $("#datatable_data").data("first"),
        last: $("#datatable_data").data("last"),
        next: $("#datatable_data").data("next"),
        previous: $("#datatable_data").data("prev"),
    },
};

// Notification List
$(document).ready(function () {
    notificationList();

    // Attach click event for mark all as read
    $(".markallread").on("click", function () {
        markAllRead();
    });

    $(".cancelnotify").on("click", function () {
        $(".notification-dropdown").removeClass("show");
    });
});

function notificationList() {
    let adminid = $("body").data("authid");
    $.ajax({
        url: "/api/notification/notificationlist",
        type: "POST",
        data: { type: "admin", authid: adminid },
        headers: {
            Authorization: "Bearer " + localStorage.getItem("admin_token"),
            Accept: "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.code == "200") {
                var data = response.data["notifications"];
                var authuser = response.data["auth_user"];
                var count = response.data["count"];

                let bell_count_div = $(".bellcount");
                if (count > 0) {
                    const html = `<span class="notification-dot position-absolute start-80 translate-middle p-1 bg-danger border border-light rounded-circle">
                    </span>`;
                    bell_count_div.html(html);
                } else {
                    bell_count_div.empty();
                }

                if (data != "") {
                    const belldiv = $("#notification-data");
                    belldiv.empty();
                    data.forEach((val) => {
                        let profileImage = "/assets/img/profile-default.png";
                        if (
                            authuser == val.from_user_id ||
                            authuser == val.to_user_id
                        ) {
                            profileImage = val.from_profileimg;
                        } else {
                            profileImage = val.to_profileimg;
                        }
                        var bellhtml = `<div class="border-bottom mb-3 pb-3">
                                        <div class="d-flex">
                                            <span class="avatar avatar-lg me-2 flex-shrink-0">
                                                <img src="${profileImage}" alt="Profile" class="rounded-circle">
                                            </span>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center">
                                                <p class="mb-1 w-100">`;
                        if (authuser == val.from_user_id) {
                            if (val.from_description) {
                                bellhtml += `${val.from_description}</p>`;
                            }
                        } else {
                            if (val.to_description) {
                                bellhtml += `${val.to_description} </p>`;
                            }
                        }
                        bellhtml += `<span class="d-flex justify-content-end "> <i class="ti ti-point-filled text-primary"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                </div>`;
                        belldiv.append(bellhtml);
                    });
                } else {
                    const belldiv = $("#notification-data");
                    belldiv.empty();
                    $(".markallread").hide();
                    let msg = $("#notification-data").data("empty_info");
                    bellhtml = `<div class="text-center">` + msg + `</div><br>`;
                    $("#notification-data").html(bellhtml);
                }
            }
        },
    });
}

function markAllRead() {
    let adminid = localStorage.getItem("user_id");
    $.ajax({
        url: "/api/notification/updatereadstatus",
        type: "POST",
        data: { type: "admin", authid: adminid },
        headers: {
            Authorization: "Bearer " + localStorage.getItem("admin_token"),
            Accept: "application/json",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.code == "200") {
                notificationList();
            }
        },
        error: function (xhr, status, error) {
            toastr.error("An error occurred while update data.");
        },
    });
}

let selectedFiles = new Map();
const logoImage = document.getElementById("logo");

if (logoImage) {
    logoImage.addEventListener("change", function (event) {
        let previewContainer = document.getElementById("edit_image_preview");
        let files = Array.from(event.target.files);

        files.forEach((file) => {
            if (!selectedFiles.has(file.name)) {
                selectedFiles.set(file.name, file);

                let reader = new FileReader();
                reader.onload = function (e) {
                    let previewDiv = document.createElement("div");
                    previewDiv.className =
                        "d-flex flex-column align-items-center justify-content-between p-2 border rounded mb-3";
                    previewDiv.dataset.fileName = file.name;
                    previewDiv.innerHTML = `
                        <div class="image-preview position-relative" style="display: flex">
                            <img src="${e.target.result}" class="img-thumbnail border" style="width: 155px; height: 155px;">
                        </div>
                        <button type="button" class="btn btn-danger btn-sm w-50 mt-2" onclick="removeImageNew('${file.name}', this)">
                            Delete
                        </button>
                    `;
                    previewContainer.appendChild(previewDiv);
                };
                reader.readAsDataURL(file);
            }
        });

        updateFileInput();
    });
}

$(document).ready(function () {
    $(".language-select").on("click", function () {
        const languageId = $(this).data("id");
        const url = `/adminLanguagedefault/${languageId}`;

        $.ajax({
            url: url,
            method: "GET",
            data: {
                _token: "{{ csrf_token() }}",
            },
            success: function (response) {
                location.reload();
            },

            error: function (xhr, status, error) {
                toastr.error("An error occurred: " + error);
            },
        });
    });
});