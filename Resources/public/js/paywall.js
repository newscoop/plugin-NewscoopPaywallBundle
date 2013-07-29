/**
 * @package Newscoop\PaywallBundle
 * @author Rafał Muszyński <rafal.muszynski@sourcefabric.org>
 * @copyright 2013 Sourcefabric o.p.s.
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 */
function format(item) { return item.name; };
function formatDiv(item) { return "<div class='select2-results'>" + item.name + "</div>"; }
<!--
$(document).ready(function() {
    var translations = {'name.error': 'Name of subscription already exists or field is empty!', 
        'step2.error.selects.blank': 'You must fill all selects.', 
        'step2.select.publication.label': 'Select publication',
        'step2.select.issue.label': 'Select an issue',
        'step2.select.section.label': 'Select section',
        'step2.select.article.label': 'Select an article',
        'step2.label.js.issue': 'Issues',
        'step2.label.js.section': 'Sections',
        'step2.label.js.article': 'Articles',
        'day': 'day',
        'days': 'days',
    };
    $('#step2').hide();
    $('#step1').show();
    var subscription_name = $('#subscriptionconf_name');
    $("#next").click(function(){
        var titleIssue = $('#title-issue');
        var titleSection = $('#title-section');
        var titleArticle = $('#title-article');
        $('.errors').empty();
        if (subscription_name.val() && window.location.pathname === $('#confForm').attr('action')) {
            subscription_name.data('validName', true);
        }
        if (subscription_name.data('validName')) {
            $.ajax({
                type: "POST",
                url: $('#confForm').attr("action"),
                data: $('#confForm').serialize(),
                dataType: "json",
                success: function(msg){
                    if (msg.status === true) {
                        var type = $('#subscriptionconf_type').val();
                        $('#step1').hide();
                        $('#step2').show();
                        $('#titleBox').append(subscription_name.val());
                        $('#typeBox').append($('#subscriptionconf_type').val());
                        $('#durationBox').append($('#subscriptionconf_range').val() == 1 ? $('#subscriptionconf_range').val()+' '+translations['day']: $('#subscriptionconf_range').val()+' '+translations['days']);
                        $('#valueBox').append($('#subscriptionconf_price').val());
                        $('#currencyBox').append($('#subscriptionconf_currency').val());
                        
                        if (type == 'publication' || type == 'issue' || type == 'section' || type == 'article') {
                            titleIssue.empty();
                            titleSection.empty();
                            titleArticle.empty();
                            $('#selectIssues').prop('disabled', 'disabled');
                            $("#selectPublications").select2({
                                placeholder: translations['step2.select.publication.label'],
                                ajax: {
                                    url: Routing.generate('newscoop_paywall_admin_getpublications'),
                                    dataType: 'json',
                                    results: function (data) {
                                        return {results: data};
                                    }
                                },
                                initSelection: function(element, callback) {
                                    var data = {id: element.val(), text: element.val()};
                                    callback(data);
                                },
                                formatResult: formatDiv,
                                formatSelection: format,
                                escapeMarkup: function (m) { return m; }

                            }).on("change", function (e) {
                                $("#selectIssues").select2("enable", true);
                                $('#subscriptionName').attr('value', $('#subscriptionconf_name').val());
                                $('#specificationForm_publication').attr('value', $("#selectPublications").select2("val"));         
                            });
                        }

                        if (type == 'issue' || type == 'section' || type == 'article') {
                            if (type == 'issue') {
                                titleIssue.append(translations['step2.label.js.issue']+': ');
                            }
                            titleSection.empty();
                            titleArticle.empty();
                            $('#selectSections').prop('disabled', 'disabled');
                            $("#selectIssues").select2({
                                placeholder: translations['step2.select.issue.label'],
                                ajax: {
                                    url: Routing.generate('newscoop_paywall_admin_getissues'),
                                    dataType: 'json',
                                    data: function () {
                                        return {
                                            publicationId: $("#selectPublications").select2("val")
                                        };
                                    },
                                    results: function (data) {
                                        return {results: data};
                                    }
                                },
                                initSelection: function(element, callback) {
                                    var data = {id: element.val(), text: element.val()};
                                    callback(data);
                                },
                                formatResult: formatDiv,
                                formatSelection: format,
                                escapeMarkup: function (m) { return m; }
                            }).on("change", function (e) {
                                $("#selectSections").select2("enable", true);
                                $('#specificationForm_issue').attr('value', $("#selectIssues").select2("val"));           
                            });
                        }

                        if (type == 'section' || type == 'article') {
                            titleIssue.append(translations['step2.label.js.issue']+': ');
                            titleSection.append(translations['step2.label.js.section']+': ');
                            titleArticle.empty();
                            $('#selectArticles').prop('disabled', 'disabled');
                            $("#selectSections").select2({
                                placeholder: translations['step2.select.section.label'],
                                ajax: {
                                    url: Routing.generate('newscoop_paywall_admin_getsections'),
                                    dataType: 'json',
                                    data: function () {
                                        return {
                                            publicationId: $("#selectPublications").select2("val"),
                                            issueId: $("#selectIssues").select2("val")
                                        };
                                    },
                                    results: function (data) {
                                        return {results: data};
                                    }
                                },
                                initSelection: function(element, callback) {
                                    var data = {id: element.val(), text: element.val()};
                                    callback(data);
                                },
                                formatResult: formatDiv,
                                formatSelection: format,
                                escapeMarkup: function (m) { return m; }
                            }).on("change", function (e) {
                                $("#selectArticles").select2("enable", true);
                                $('#specificationForm_section').attr('value', $("#selectSections").select2("val"));          
                            }); 
                        }

                        if (type == 'article') {
                            titleArticle.append(translations['step2.label.js.article']+': ');
                            $("#selectArticles").select2({
                                placeholder: translations['step2.select.article.label'],
                                ajax: {
                                    url: Routing.generate('newscoop_paywall_admin_getarticles'),
                                    dataType: 'json',
                                    data: function () {
                                        return {
                                            publicationId: $("#selectPublications").select2("val"),
                                            issueId: $("#selectIssues").select2("val"),
                                            sectionId: $("#selectSections").select2("val")
                                        };
                                    },
                                    results: function (data) {
                                        return {results: data};
                                    }
                                },
                                initSelection: function(element, callback) {
                                    var data = {id: element.val(), text: element.val()};
                                    callback(data);
                                },
                                escapeMarkup: function (m) { return m; }
                            }).on("change", function (e) {
                                $('#specificationForm_article').attr('value', $("#selectArticles").select2("val"));
                            });
                        }
                        if (window.location.pathname === $('#confForm').attr('action')) {
                            $.post(Routing.generate('newscoop_paywall_admin_getall'), {
                                'publicationId': $("#specificationForm_publication").val(),
                                'issueId': $("#specificationForm_issue").val(),
                                'sectionId': $("#specificationForm_section").val(),
                                'articleId': $("#specificationForm_article").val()
                            }, function (data) {
                                $("#s2id_selectPublications .select2-choice .select2-chosen").empty();
                                $("#s2id_selectPublications .select2-choice .select2-chosen").append(data.Publications[0].name);
                                $("#s2id_selectIssues .select2-choice .select2-chosen").empty();
                                $("#s2id_selectIssues .select2-choice .select2-chosen").append(data.Issues[0].name);
                                $("#s2id_selectSections .select2-choice .select2-chosen").empty();
                                $("#s2id_selectSections .select2-choice .select2-chosen").append(data.Sections[0].name);
                                $("#s2id_selectArticles .select2-choice .select2-chosen").empty();
                                $("#s2id_selectArticles .select2-choice .select2-chosen").append(data.Articles[0].text);
                            }, 'json');
                        }
                    } else {
                        $('#next').prop("disabled", false);
                        var ul = $('<ul></ul>');
                        ul.appendTo($('.errors'));
                        $('.errors').css('color', '#FF2200');
                        $.each($.parseJSON(msg.errors), function (i, obj) {
                            ul.append('<li>'+obj+'</li>');
                        });
                    }
                }
            });

        } else {
            $('.errors').css('color', '#FF2200').append('<ul><li>'+translations['name.error']+'</li></ul>');
        }

        return false;      
    });

    $('#save').click(function(e) {
        if(!$("#selectIssues").select2("val") || !$("#selectSections").select2("val") || !$("#selectArticles").select2("val")) {
            alert(translations['step2.error.selects.blank']);
            return false;
        }
    });

    $('#skip').click(function() {
        window.location.href = Routing.generate('newscoop_paywall_managesubscriptions_manage');
    });

    var delay = (function(){
        var timer = 0;
        return function(callback, ms){
            clearTimeout (timer);
            timer = setTimeout(callback, ms);
        };
    })();

    $('#subscriptionconf_name').change(function() {
        $.post(Routing.generate('newscoop_paywall_admin_check'), {
            'subscriptionName': $(this).val()
        }, function (data) {
            if (data.status) {
                subscription_name.css('color', 'rgb(0, 128, 0)');
                subscription_name.data('validName', true);
                $('.errors').empty();
            } else {
                subscription_name.css('color', 'rgb(255, 0, 0)');
                subscription_name.data('validName', false);
                if (window.location.pathname === $('#confForm').attr('action')) {
                    subscription_name.css('color', 'rgb(0, 128, 0)');
                }
            }
        }, 'json');
    }).keyup(function() {
        delay(function(){
            $('#subscriptionconf_name').change();
        }, 1000 );
    });
});
//-->