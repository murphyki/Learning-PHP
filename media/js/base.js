function mainmenu() {
    // Original menu code...
    /*$(" #nav ul ").css({display: "none"}); // Opera Fix
    $(" #nav li").hover(function() {
        $(this).find('ul:first').css({visibility: "visible",display: "none"}).slideDown(400);
        }, function(){
            $(this).find('ul:first').css({visibility: "hidden"});
        }
    );*/
    
    $('ul.sf-menu').superfish({delay: 200});
}

function handle_ajax_error(jqXHR, textStatus, errorThrown) {
    var msg = "";
    if (jqXHR.status === 0) {
        msg = 'No connection.\n Verify Network.';
        //window.location = "/error.php";
    } else if (jqXHR.status == 404) {
        msg = 'Requested page not found. [404]';
        window.location = "/404.php";
    } else if (jqXHR.status == 500) {
        msg = 'Internal Server Error [500].\n' + jqXHR.responseText;
        window.location = "/500.php";
    } else if (textStatus === 'parsererror') {
        msg = 'Requested JSON parse failed.';
        window.location = "/error.php";
    } else if (textStatus === 'timeout') {
        msg = 'Time out error.';
        //window.location = "/error.php";
    } else if (textStatus === 'abort') {
        msg = 'Ajax request aborted.';
        //window.location = "/error.php";
    } else {
        msg = 'Uncaught Error.\n' + jqXHR.responseText;
        //window.location = "/error.php";
    }
}

function enable_row_hover() {
    $('tr.even').hover(
        function() {
            // over
            $(this).addClass('even_hover');
        }, 
        function() {
            // out
            $(this).removeClass('even_hover');
        }
    );

    $('tr.odd').hover(
        function() {
            // over
            $(this).addClass('odd_hover');
        }, 
        function() {
            // out
            $(this).removeClass('odd_hover');
        }
    );
}

function check_for_updates(token, callback) {
    $.ajax({
        cache: false,
        type: "GET",
        data: {
            action: "check_for_updates",
            token: token,
            requestedWith: "xmlhttprequest"
        },
        url: "controller.php",
        success: function(data, status) {
            if (data && data === "true") {
                callback();
            }
        },
        error: function (jqXHR, textStatus, errorThrown){
            handle_ajax_error(jqXHR, textStatus, errorThrown);
        }  
    });
}

function load_html_data(container_class, params, callback) {
    params["requestedWith"] = "xmlhttprequest";
    $("." + container_class).fadeOut(500, function() {
        $.ajax({
            cache: false,
            type: "GET",
            data: params,
            url: "controller.php",
            success: function(data, status) {
                if (data) {
                    callback(data);
                }
            },
            error: function (jqXHR, textStatus, errorThrown){
                handle_ajax_error(jqXHR, textStatus, errorThrown);
            }  
        });
    });
}

function load_json_data(params, callback) {
    params["requestedWith"] = "xmlhttprequest";
    $.ajax({
        cache: false,
        type: "GET",
        dataType: "json",
        data: params,
        url: "controller.php",
        success: function(data, status) {
            if (data) {
                callback(data);
            }
        },
        error: function (jqXHR, textStatus, errorThrown){
            handle_ajax_error(jqXHR, textStatus, errorThrown);
        }  
    });
}

function load_selector(selector_id, params) {
    params["requestedWith"] = "xmlhttprequest";
    $.ajax({
        cache: false,
        type: "GET",
        data: params,
        url: "controller.php",
        success: function(data, status) {
            var selectionBefore = $("#" + selector_id + " option:selected").val();
            $("#" + selector_id).html(data);
            var selectionAfter = $("#" + selector_id + " option:selected").val();
            if (selectionAfter != selectionBefore) {
                $("select").change();
            }
        },
        error: function (jqXHR, textStatus, errorThrown){
            handle_ajax_error(jqXHR, textStatus, errorThrown);
        }  
    });
}

