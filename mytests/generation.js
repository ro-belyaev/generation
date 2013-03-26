//<!--

$(function() {
    var getParams = window.location.search.substr(1);
    var getParamsArr = getParams.split ("&");
    var params = {};

    for ( var i = 0; i < getParamsArr.length; i++) {
        var tmpArr = getParamsArr[i].split("=");
        params[tmpArr[0]] = tmpArr[1];
    }

    var generationWasCanceled = false;

    $('#cancel').on('click', function() {
        $.ajax({
            type: "GET",
            url: "./generation_cancel",
            data: {
                "id": params['id']
            }
        })
            .done(function() {
                generationWasCanceled = true;
                window.open('./main.php', '_top', '', true);
            });
    });

    var intervalID = setInterval(function() {
        console.log('before ajax');
        $.ajax({
            type: "GET",
            url: './check_generation_process.php',
            timeout: 3900,
            data: {
                "id": params['id']
            },
            dataType: "json"
        })
            .done(function(progress) {
                console.log('process...');
                var progressBar = progress['progress'];
                $('#progress-bar').width(progressBar + '%');
                if(generationWasCanceled || progressBar == 100) {
                    clearInterval(intervalID);
                }
            });
    }, 4000);
});

//-->