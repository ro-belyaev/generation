$(function() {
    $.ajax({
        url : "./transform_xml.php",
        dataType : "json"
    })
        .done(function(data) {
//            console.log('begin');
            console.log(data);
            var xml = data['xml'];
            var tree = data['tree'];
//            var dependenceBetweenClasses = data['dependence_between_classes'];
//            var dependenceBetweenCriterions = data['dependence_between_criterions'];
//            console.log(dependenceBetweenClasses);
//            console.log(dependenceBetweenCriterions);
            $('#tests')
                .on('loaded.jstree', function(event) {
                    $('[data-type="hidden"]')
                        .each(function() {
                            console.log(this);
                            $(this).hide();
                        });

                    $('#start').on('click', function () {
                        unCheckHiddenNodes(tree);
                        var tests_ids = [],
                            tests = $.jstree._reference('#tests');
                        $('#alert-message').alert('close');
                        $('#tests')
                            .jstree('deselect_all')
                            .jstree('get_checked', null, true).each(function () {
                                if(tests._get_children(this).length == 0){
                                    tests_ids.push(this.id);
                                }
                            });
                        console.log(tests_ids);
                        checkHiddenNodes(tests_ids, tree);
//                        if(checkDependencesBetweenCriterions(tests_ids, tree) &&
//                            checkDependencesBetweenClasses(tests_ids, tree)) {
                            $.ajax({
                                type: "POST",
                                url: "start_generation.php",
                                data: {
                                    tests: tests_ids
                                },
                                success: function(data) {
                                    console.log(data);
                                }
                            })
                                .fail(function () {
                                    alert("При запуске генерации тестового набора произошла ошибка.");
                                });
//                        }
                    });
                })
                .jstree({
                    "xml_data" : {
                        "data" : xml,
                        "xsl" : "nest"
                    },
                    "plugins" : ["themes", "xml_data", "ui", "checkbox"]
                });
        })
        .fail(function() {
            alert('На ajax-запрос с сервера не пришёл ответ. Пожалуйста, перезагрузите страницу');
        })

});