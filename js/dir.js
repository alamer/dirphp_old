function getQueryParams(qs) {
    qs = qs.split("+").join(" ");
    var params = {},
            tokens,
            re = /[?&]?([^=]+)=([^&]*)/g;

    while (tokens = re.exec(qs)) {
        params[decodeURIComponent(tokens[1])]
                = decodeURIComponent(tokens[2]);
    }

    return params;
}

function hideAll()
{
    $("#login").hide();
    $(".w5").hide();
    $(".w6").hide();
    $(".rename").hide();
    $(".remove").hide();
    $("#forauth").hide();
    $("#loginHide").hide();
    $("#loginShow").show();
}
function showAll()
{
    $(".w5").show();
    $(".w6").show();
    $(".rename").show();
    $(".remove").show();
    $("#forauth").show();
    $("#loginHide").show();
    $("#loginShow").hide();
}

function clearCookies()
{
    $.cookie("status", null, {path: '/'});
    $.cookie("username", null, {path: '/'});
    $.cookie("password", null, {path: '/'});
}

$(document).ready(function() {
    console.log("ready!");
    hideAll();
    $(document).on('click', '#loginShow', function() {
        // code here
        $("#login").show();
        console.log("click!");
    });
    $(document).on('click', '#loginHide', function() {
        // code here
        clearCookies();
        hideAll();
        console.log("click!");
    });
    $(".rename").button();
    $(".remove").button();
    $("#authAction").button();
    $("#createdir").button();
    $("#upload-btn").button();
    var sizeBox = document.getElementById('sizeBox'), // container for file size info
            progress = document.getElementById('progress'); // the element we're using for a progress bar
    var $_GET = getQueryParams(document.location.search);
    var  wrap = document.getElementById('pic-progress-wrap');
    var uploader = new ss.SimpleUpload({
        button: 'upload-btn', // file upload button
        url: 'uploadHandler.php', // server side handler
        progressUrl: 'uploadProgress.php', // enables cross-browser progress support (more info below)
        name: 'uploadfile', // upload parameter name        
        responseType: 'json',
        allowedExtensions: ['jpg', 'jpeg', 'png', 'gif', 'zip', '7z', 'rar', 'exe'],
        maxSize: 1024 * 100, // kilobytes
        hoverClass: 'ui-state-hover',
        focusClass: 'ui-state-focus',
        disabledClass: 'ui-state-disabled',
        data: {'fold1': $_GET['fold']},
        onSubmit: function(filename, extension) {
            var prog = document.createElement('div'),
                    outer = document.createElement('div'),
                    bar = document.createElement('div'),
                    size = document.createElement('div'),
                    self = this;

            prog.className = 'prog';
            size.className = 'size';
            outer.className = 'progress';
            bar.className = 'bar';

            outer.appendChild(bar);
            prog.appendChild(size);
            prog.appendChild(outer);
            wrap.appendChild(prog); // 'wrap' is an element on the page

            self.setProgressBar(bar);
            self.setProgressContainer(prog);
            self.setFileSizeBox(size);
            if ($.inArray(extension, this.allowedExtensions))
            {
                $("#dialogload").dialog("open");
            }
        },
        onSizeError: function(filename, fileSize) {
            alert("Файл слишком большой");
        },
        onExtError: function(filename, extension) {
            alert("Недопустимое разрешение файла");
        },
        onComplete: function(filename, response) {
            $("#dialogload").hide();
            if (!response) {
                alert(filename + 'upload failed');
                return false;
            }
            if (response.success == true)
            {
               // location.reload();
            }
            else
            {
                alert('Произошла ошибка при загрузке файла' + response.msg);
            }

            // do something with response...
        }
    });
    //authAction
    $("#authAction").click(function()
    {
        //Send ajax 
        var username = $("#username").val();
        var password = $("#password").val();
        console.log(username);
        console.log(password);
        if (username === "" || password === "")
        {
            alert("Введите данные для авторизации");
        }
        else
        {
            $.post('ajax.php', {username: username, password: password, action: "AUTH"}, function(data) {
                if (data === 'OK')
                {
                    $.cookie("status", data, {expires: 10, path: '/'});
                    $.cookie("username", username, {expires: 10, path: '/'});
                    $.cookie("password", password, {expires: 10, path: '/'});
                    $("#login").hide();
                    showAll();
                }
                else
                {
                    clearCookies();
                    hideAll();
                }
            });
        }
    });
    if ($.cookie("status") === 'OK')
    {
        //alert("Все нормуль");
        //
        showAll();
    }
    else
    {
        //alert("Нет авторизации");
        hideAll();
    }
    $(".rename").click(function() {
        var $item = $(this).closest("tr")   // Finds the closest row <tr> 
                .find(".w2")     // Gets a descendent with class="nr"
                .text(); // Retrieves the text within <td>
        $("#olditem").val($item)
        $("#newitem").val($item)
        $("#dialog").dialog("open");
    });
    $("#createdir").click(function() {
        $("#dialogdir").dialog("open");
    });
    $(".remove").click(function() {
        var $item = $(this).closest("tr")   // Finds the closest row <tr> 
                .find(".w2")     // Gets a descendent with class="nr"
                .text(); // Retrieves the text within <td>

        var $_GET = getQueryParams(document.location.search);
        $.post('ajax.php', {fold: $_GET['fold'], item: $item, action: "REMOVE"}, function(data) {
            if (data === "OK")
            {
                location.reload();
            }
            else
            {
                alert("Не удалось удалить объект");
            }
        });
    });

    $(function() {
        $("#dialog").dialog({
            autoOpen: false,
            show: {
                effect: "blind",
                duration: 1000
            },
            hide: {
                effect: "explode",
                duration: 1000
            },
            buttons: {
                "Переименовать": function() {
                    var $_GET = getQueryParams(document.location.search);
                    $.post('ajax.php', {fold: $_GET['fold'], item: $("#olditem").val(), newitem: $("#newitem").val(), action: "RENAME"}, function(data) {
                        if (data === "OK")
                        {
                            location.reload();
                        }
                        else
                        {
                            alert("Не удалось удалить объект");
                        }
                    });

                    $(this).dialog("close");
                },
                "Отмена": function() {
                    $(this).dialog("close");
                }
            }
        });
    });
    $(function() {
        $("#dialogload").dialog({
            autoOpen: false,
            show: {
                effect: "blind",
                duration: 1000
            },
            hide: {
                effect: "explode",
                duration: 1000
            }
        });

    });
    $(function() {
        $("#dialogdir").dialog({
            autoOpen: false,
            show: {
                effect: "blind",
                duration: 1000
            },
            hide: {
                effect: "explode",
                duration: 1000
            },
            buttons: {
                "Создать": function() {
                    var $_GET = getQueryParams(document.location.search);
                    $.post('ajax.php', {fold: $_GET['fold'], item: $("#newdir").val(), action: "CREATE"}, function(data) {
                        if (data === "OK")
                        {
                            location.reload();
                        }
                        else
                        {
                            alert("Не удалось создать объект");
                        }
                    });

                    $(this).dialog("close");
                },
                "Отмена": function() {
                    $(this).dialog("close");
                }
            }
        });
    });
});