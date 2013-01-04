$(document).ready(function () {
        $(".hide-if-js").hide();
        $(".hide-if-no-js").show();

        $("#dialog").dialog({
            autoOpen:false,
            show:"blind",
            hide:"explode"
        });

        $("#meta-control").tabs();

        $("#loading-holder").hide();

        $("#loading-holder").ajaxStart(function () {
            $(this).show();
        }).ajaxStop(function () {
                $(this).hide();
            });

        $('#meta-title').keyup(function () {
            $('input[name="title-length"]').val(this.value.replace(/{.*}/g, '').length);
            if (this.value.replace(/{.*}/g, '').length > 70) {
                $('input[name="title-length"]').parent().parent().addClass('error');
            } else {
                $('input[name="title-length"]').parent().parent().removeClass('error');
            }
        });
        $('#meta-description').keyup(function () {
            $('input[name="description-length"]').val(this.value.replace(/{.*}/g, '').length);
            if (this.value.replace(/{.*}/g, '').length > 160) {
                $('input[name="description-length"]').parent().parent().addClass('error');
            } else {
                $('input[name="description-length"]').parent().parent().removeClass('error');
            }
        });

        $(function () {
            $("#dialog-confirm").dialog({
                resizable:false,
                height:170,
                modal:true,
                autoOpen:false,
                closeText:"hide"
            });
        });

//Default Button click
        $("#default-button").on("click", function () {
            //ajax request to save meta
            test();
        });

//Add meta Button click
        $("#add-meta-button").on("click", function () {
            //ajax request to save meta
            addMetaElement();
        });

        $.widget("ui.combobox", {
            _create:function () {
                var input,
                    that = this,
                    select = this.element.hide(),
                    selected = select.children(":selected"),
                    value = selected.val() ? selected.text() : "",
                    wrapper = this.wrapper = $("<span>")
                        .addClass("ui-combobox")
                        .insertAfter(select);

                function log(message) {
                    $("<div>").text(message).prependTo("#log");
                    $("#log").scrollTop(0);
                }

                function removeIfInvalid(element) {
                    var value = $(element).val(),
                        matcher = new RegExp("^" + $.ui.autocomplete.escapeRegex(value) + "$", "i"),
                        valid = false;
                    select.children("option").each(function () {
                        if ($(this).text().match(matcher)) {
                            this.selected = valid = true;
                            return false;
                        }
                    });
                    if (!valid) {
                        // remove invalid value, as it didn't match anything
                        $(element)
                            .val("")
                            .attr("title", value + " didn't match any item")
                            .tooltip("open");
                        select.val("");
                        setTimeout(function () {
                            input.tooltip("close").attr("title", "");
                        }, 2500);
                        input.data("autocomplete").term = "";
                        return false;
                    }
                }

                var meta = [];

                input = $("<input>")
                    .appendTo(wrapper)
                    .val(value)
                    .attr("title", "")
                    .addClass("ui-state-default ui-combobox-input")
                    .autocomplete({
                        delay:0,
                        minLength:0,
                        source:function (request, response) {
                            var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
                            response(select.children("option").map(function () {
                                var text = $(this).text();
                                if (this.value && ( !request.term || matcher.test(text) ))
                                    return {
                                        label:text.replace(
                                            new RegExp(
                                                "(?![^&;]+;)(?!<[^<>]*)(" +
                                                    $.ui.autocomplete.escapeRegex(request.term) +
                                                    ")(?![^<>]*>)(?![^&;]+;)", "gi"
                                            ), "<strong>$1</strong>"),
                                        value:text,
                                        option:this
                                    };
                            }));
                        },
                        select:function (event, ui) {
                            $('.addtional-meta-group').remove();

                            ui.item.option.selected = true;
                            that._trigger("selected", event, {
                                item:ui.item.option
                            });

                            var page = $("#combobox").val();
                            alert($("#combobox option:selected").val());
                            alert($("#combobox option:selected").text());
                            $("#main-page").val(page);
                            //code to send ajax request to get page meta info here
                            $.ajax({
                                url:'ri.php/riseo/ajax_get_page_meta/',
                                data:{ page:page },
                                dataType:'json',
                                type:'post',
                                success:function (response) {
                                    if (response != null) {
                                        $("#seo-id").val(response.seo_id);
                                        $("#page-id").val(response.page_id);
                                    } else {
                                        $("#seo-id").val('');
                                        $("#page-id").val('');
                                    }
                                    $(".meta-input").val(null);
                                    if (response.metas != null) {
                                        $.each(response.metas, function (key, value) {
                                            switch (key) {
                                                case "title":
                                                    $("#meta-title").val(value);
                                                    break;
                                                case "description":
                                                    $("#meta-description").val(value);
                                                    break;
                                                case "keywords":
                                                    $("#meta-keywords").val(value);
                                                    break;
                                                case "robots":
                                                    $("input[name='metas[robots]'][value='" + value + "']")
                                                        .attr('checked', true);
                                                    break;
                                                default :
                                                    appendAdditionalMeta(key, value);
                                            }
                                        });
                                    }

                                    //update character counters
                                    $('input[name="title-length"]').val($("#meta-title").val().replace(/{.*}/g, '').length);
                                    $('input[name="description-length"]').val($("#meta-description").val().replace(/{.*}/g, '').length);

                                    if ($("#save-button").attr('disabled') == 'disabled') {
                                        $("#save-button").removeAttr('disabled');
//                                        $("#default-button").removeAttr('disabled');
                                        $("#add-meta-button").removeAttr('disabled');
                                    }
                                }
                            });


//                            log(ui.item ?
//                                "Selected: " + ui.item.label :
//                                "Nothing selected, input was " + this.value);
                        },
                        change:function (event, ui) {
                            if (!ui.item)
                                return removeIfInvalid(this);
                        }
                    })
                    .addClass("ui-widget ui-widget-content ui-corner-left");

                input.data("autocomplete")._renderItem = function (ul, item) {
                    return $("<li>")
                        .data("item.autocomplete", item)
                        .append("<a>" + item.label + "</a>")
                        .appendTo(ul);
                };

                $("<a>")
                    .attr("tabIndex", -1)
                    .attr("title", "Show All Items")
                    .tooltip()
                    .appendTo(wrapper)
                    .button({
                        icons:{
                            primary:"ui-icon-triangle-1-s"
                        },
                        text:false
                    })
                    .removeClass("ui-corner-all")
                    .addClass("ui-corner-right ui-combobox-toggle")
                    .click(function () {
                        // close if already visible
                        if (input.autocomplete("widget").is(":visible")) {
                            input.autocomplete("close");
                            removeIfInvalid(input);
                            return;
                        }

                        // work around a bug (likely same cause as #5265)
                        $(this).blur();

                        // pass empty string as value to search for, displaying all results
                        input.autocomplete("search", "");
                        input.focus();
                    });

                input
                    .tooltip({
                        position:{
                            of:this.button
                        },
                        tooltipClass:"ui-state-highlight"
                    });
            },

            destroy:function () {
                this.wrapper.remove();
                this.element.show();
                $.Widget.prototype.destroy.call(this);
            }
        });

//prepare ajaxForm
        $('#meta-form').ajaxForm({
            dataType:'json',
            beforeSubmit:showRequest, // pre-submit callback
            success:showResponse, // post-submit callback
            type:'post',
            url:'ri.php/riseo/ajax_save_page_meta/'
        });

        $(function () {
            $("#combobox").combobox();
            $("#toggle").click(function () {
                $("#combobox").toggle();
            });
        });
    }

)
;

// pre-submit callback
function showRequest(formData, jqForm, options) {
    // formData is an array; here we use $.param to convert it to a string to display it
    // but the form plugin does this for you automatically when it submits the data
    var queryString = $.param(formData);

    // jqForm is a jQuery object encapsulating the form element.  To access the
    // DOM element for the form do this:
    // var formElement = jqForm[0];

//    alert('About to submit: \n\n' + queryString);

    // here we could return false to prevent the form from being submitted;
    // returning anything other than false will allow the form submit to continue
    return true;
}

// post-submit callback
function showResponse(responseText, statusText, xhr, $form) {
    // for normal html responses, the first argument to the success callback
    // is the XMLHttpRequest object's responseText property

    // if the ajaxForm method was passed an Options Object with the dataType
    // property set to 'xml' then the first argument to the success callback
    // is the XMLHttpRequest object's responseXML property

    // if the ajaxForm method was passed an Options Object with the dataType
    // property set to 'json' then the first argument to the success callback
    // is the json data object returned by the server
//
//    alert('status: ' + statusText + '\n\nresponseText: \n' + responseText +
//        '\n\nThe output div should have already been updated with the responseText.');

    if (statusText == 'success') {
//        $("#dialog p").html(responseText);
//        $("#dialog").dialog("open");
        if (responseText != null) {
            $("#seo-id").val(responseText);
        }
    }
}

function addMetaElement() {
    var metaElement =
        '<div class="controls addtional-meta-group">' +
            '<input type="text" class="meta-input" name="add-meta-name[]" placeholder="Enter meta name..." />' +
            '<textarea class="meta-input" name="add-meta-content[]" placeholder="Enter meta content..."></textarea>' +
            '<button type="button" class="btn btn-danger meta-delete btn-mini"><i class="icon-minus-sign icon-white"></i>' +
            '</div>';

    $("#addtional-metas").append(metaElement);
    bindDeleteButton();

    return false;
}

function appendAdditionalMeta(key, value) {
    var metaElement =
        '<div class="controls addtional-meta-group">' +
            '<input type="text" class="meta-input" name="add-meta-name[]" value="' + key + '" />' +
            '<textarea class="meta-input" name="add-meta-content[]">' + value + '</textarea>' +
            '<button type="button " class="btn btn-danger meta-delete ajax-button btn-mini"><i class="icon-minus-sign icon-white"></i>' +
            '</div>';

    $("#addtional-metas").append(metaElement);
    bindDeleteButton();

    return false;
}

function bindDeleteButton() {
    $('.meta-delete').on('click', function () {
        var buttonDom = $(this);
        var metaDOM = $(this).parent();
        if ($(this).hasClass('ajax-button')) {
            var meta_data = [];
            meta_data['seo_id'] = $("#seo-id").val();
            meta_data['meta_name'] = $(this).parent().find('input.meta-input:text').val();
        }
        $("#dialog-confirm").dialog({
            buttons:{
                "Yes":function () {
                    if (buttonDom.hasClass('ajax-button')) {
                        deleteSingleMeta(meta_data);
                    }
                    metaDOM.remove();
                    $(this).dialog("close");
                },
                "No":function () {
                    $(this).dialog("close");
                }
            }
        });
        $("#dialog-confirm").dialog("open");

        return false;
    });
}

function deleteSingleMeta(meta_data) {
    console.log(meta_data['seo_id'], meta_data['meta_name']);
    $.ajax({
        url:'ri.php/riseo/ajax_delete_single_meta/',
        data:{ seo_id:meta_data['seo_id'], meta_name:meta_data['meta_name']},
        dataType:'json',
        type:'post',
        success:function (response) {

            return true;
        }
    });
}