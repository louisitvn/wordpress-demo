/* ------------------------------------------------------------------------------
*
*  # Styled checkboxes, radios and file input
*
*  Specific JS code additions for form_checkboxes_radios.html page
*
*  Version: 1.0
*  Latest update: Aug 1, 2015
*
* ---------------------------------------------------------------------------- */

function updateEmbeddedForm(form, url) {
    var data = {};
    form.serializeArray().forEach(function(entry) {
        if(entry.value!=="") {
            data[entry.name] = entry.value;
        }
    });
    
    $.ajax({
        method: "GET",
        url: url,
        data: data
    })
    .done(function( msg ) {
        // $(".embedded-form-result").html($("<div>").html(msg).find(".embedded-form-result"));
        var html = $("<div>").html(msg).find(".embedded-form-result").html();
        $(".embedded-form-result").html(html);
        
        // Hightlight code
        Prism.highlightAll();
    });
}

function dashboardQuickview(item, box) {
    var id = item.val();
    var url = box.attr("data-url");
    
    $.ajax({
        method: "GET",
        url: url,
        data: {
            uid: id
        }
    })
    .done(function( msg ) {
        box.html(msg);
        // Setup chart
        $('.chart').each(function() {
            updateChart($(this));
        });
    });
}

// Update checking backend / frontend access
function updateCheckAccess() {
    if($('#backend_access').is(":checked")) {
        $('.backend-box').removeClass('hide');
    } else {
        $('.backend-box').addClass('hide');
        $('li.frontend-box a').trigger('click');
    }
    
    if($('#frontend_access').is(":checked")) {
        $('.frontend-box').removeClass('hide');
    } else {
        $('.frontend-box').addClass('hide');
        $('li.backend-box a').trigger('click');
    }
    
    if(!$('#backend_access').is(":checked") && !$('#frontend_access').is(":checked")) {
        $('.options-container').hide();
    } else {
        $('.options-container').show();
    }
}

// Preview upload image
function readURL(input, img) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            img.attr('src', e.target.result);
            
            // calculate crop part
            var box_width = img.parent().width();
            var box_height = img.parent().height();
            var width = img[0].naturalWidth;
            var height = img[0].naturalHeight;
            var cal_width, cal_height;
            
            if(width/height < box_width/box_height) {
                cal_height = box_height;
                cal_width = box_height*(height/width);
            } else {
                cal_width = box_width;
                cal_height = box_width*(width/height);
            }
            
            img.width(cal_height);
            img.height(cal_width);
            
            var mleft = -Math.abs(cal_width - box_width)/2;
            var mtop = -Math.abs(cal_height - box_height)/2;
            img.css("margin-left", mtop+"px");
            img.css("margin-top", mleft+"px");
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function popupwindow(url, title, w, h) {
  var left = (screen.width/2)-(w/2);
  var top = 0;
  return window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+screen.height+', top='+top+', left='+left);
} 

$(function() {
    // Default tooltip
    $('[data-popup=tooltip]').tooltip({
		template: '<div class="tooltip"><div class="bg-teal-800"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div></div>'
	});
    
    // Basic select2
    // ------------------------------

    // Default initialization
    $('.select').select2({
        minimumResultsForSearch: 101
    });

    // Select with search
    $('.select-search').select2({
        minimumResultsForSearch: 101
    });
    
    
    // Checkboxes/radios (Uniform)
    // ------------------------------

    // Default initialization
    $(".styled, .multiselect-container input").uniform({
        radioClass: 'choice'
    });
    
    
    // Form help text
    // ------------------------------
    
    $(".form-control").focus( function() {
        $(this).parents(".form-group").find(".help").addClass("showed");
    });

    $(".form-control").blur( function() {
        $(this).parents(".form-group").find(".help").removeClass("showed");
    });
    
    // Preview upload image    
    $(".previewable").change(function() {
        var img = $("img[preview-for='" + $(this).attr("name") + "']");
        readURL(this, img);
    });
    $(".remove-profile-image").click(function() {
        var img = $(this).parents(".profile-image").find("img");
        var imput = $(this).parents(".profile-image").find("input[name='_remove_image']");
        img.attr("src", img.attr("empty-src"));
        imput.val("true");
    });
    
    //
    // Switchery
    // ------------------------------

    // Initialize multiple switches
    $('.switchery').each(function() {
        if($(this).attr("data-switchery") != "true") {
            var switchery = new Switchery(this, {color: $(".navbar-inverse").css("background-color")});
        }
    }); 
    
    // Bootstrap switch
    // ------------------------------
    $(".switch").bootstrapSwitch();
    
    
    // Action list event
    // ------------------------------
    $(document).on("click", ".list_actions a", function(e) {
        var form = $(this).parents(".listing-form");
        var vals = form.find("input[name='ids[]']:checked").map(function () {
            return this.value;
        }).get();
        
        $(this).attr("new-href", $(this).attr("href") + "?uids=" + vals.join(","));
        $(this).attr("items-count", vals.length);
    });
    
    
    // Confirm event
    // ------------------------------
    $(document).on("click", "a[delete-confirm]", function(e) {
        var mgs = $(this).attr("delete-confirm");
        $(this).attr("new-href", $(this).attr("href"));
        // count items
        var count = 1;
        if (typeof($(this).attr("items-count")) != 'undefined') {
            count = $(this).attr("items-count");
        }
        
        $('a[data-target="#delete_confirm_model"]').trigger("click");        
        $("#delete_confirm_model h6").html(mgs.replace(":number", "<span class='text-bold text-danger'>" + count + "</span>"));
        $(".delete-confirm-button").attr("href", $(this).attr("new-href"));
        
        if(typeof($(this).attr("no-ajax")) != "undefined") {
            $(".delete-confirm-button").removeClass("ajax_link");
        } else {
            if(!$(".delete-confirm-button").hasClass("ajax_link")) {
                $(".delete-confirm-button").addClass("ajax_link");
            }
        }
        
        if($(this).parents(".list_actions").length) {
            var url = $(this).attr("new-href");
            var form = $(this).parents(".listing-form");
            var vals = form.find("input[name='ids[]']:checked").map(function () {
                return this.value;
            }).get();
            
            url = url + "?uids=" + vals.join(",");
             $(".delete-confirm-button").attr("href", url);
        }
        
        e.stopImmediatePropagation();
        e.preventDefault();
    });
    $(document).on("click", ".delete-confirm-button", function(e) {
        if($('.confirm-delete-form').valid()) {
            
        } else {
            e.stopImmediatePropagation();
            e.preventDefault();
        }        
    });
    // List delete confirm event
    // ------------------------------
    $(document).on("click", "a[list-delete-confirm]", function(e) {
        var url = $(this).attr("href");
        var curl = $(this).attr("list-delete-confirm");
        $(this).attr("new-href", $(this).attr("href"));
        
        if($(this).parents(".list_actions").length) {
            var form = $(this).parents(".listing-form");
            var vals = form.find("input[name='ids[]']:checked").map(function () {
                return this.value;
            }).get();
            
            url = url + "?uids=" + vals.join(",");
            curl = curl + "?uids=" + vals.join(",");
        }
    
        $('a[data-target="#list_delete_confirm_model"]').trigger("click");
        $(".list-delete-confirm-button").attr("href", url);
        
        // Get message
        // ajax update custom sort
        $.ajax({
            method: "GET",
            url: curl,
        })
        .done(function( msg ) {
            $("#list_delete_confirm_model .content").html(msg);
        });
        
        e.stopImmediatePropagation();
        e.preventDefault();
    });
    $(document).on("click", ".list-delete-confirm-button", function(e) {
        if($('.list-confirm-delete-form').valid()) {
            
        } else {
            e.stopImmediatePropagation();
            e.preventDefault();
        }
    });
    // Link confirm
    $(document).on("click", "a[link-confirm]", function(e) {
        var mgs = $(this).attr("link-confirm");
        var url = $(this).attr("href");
        $(this).attr("new-href", $(this).attr("href"));
        
        if($(this).parents(".list_actions").length) {
            var form = $(this).parents(".listing-form");
            var vals = form.find("input[name='ids[]']:checked").map(function () {
                return this.value;
            }).get();
            
            url = url + "?uids=" + vals.join(",");
        }
        
        // count items
        var count = 1;
        if (typeof($(this).attr("items-count")) != 'undefined') {
            count = $(this).attr("items-count");
        }
        
        $('a[data-target="#link_confirm_model"]').trigger("click");
        mgs = mgs.replace(":number", "<span class='text-bold text-teal-800'>" + count + "</span>");
        mgs = mgs.replace(":name", "<span class='text-bold text-teal-800'>" + $(this).html() + "</span>");
        
        $("#link_confirm_model h6").html(mgs);
        $(".link-confirm-button").attr("href", url);
        
        e.stopImmediatePropagation();
        e.preventDefault();
    });
    
    // List fields
    // ------------------------------
    // Change item per page
    $(document).on("click", ".add-custom-field-button", function(e) {
        var type_name = $(this).attr("type_name");
        var sample = $("."+type_name+"_sample ");
        var sample_url = $(this).attr("sample-url");
        
        // ajax update custom sort
        $.ajax({
            method: "GET",
            url: sample_url,
            data: {
                type: type_name,
            }
        })
        .done(function( msg ) {
            var index = $('.field-list tr').length;
            
            msg = msg.replace(/__index__/g, index);
            msg = msg.replace(/__type__/g, type_name);
            
            $('.field-list').append($('<div>').html(msg).find("table tbody").html());
            
            //
            // Switchery
            // ------------------------------
        
            // Initialize multiple switches
            $('.switchery').each(function() {
                if($(this).attr("data-switchery") != "true") {
                    var switchery = new Switchery(this, {color: $(".navbar-inverse").css("background-color")});
                }
            });                
        });
    });
    $(document).on("click", ".remove-not-saved-field", function(e) {
        $('tr[parent="'+$(this).parents('tr').attr('rel')+'"]').remove();
        $(this).parents('tr').remove();
    });
    $(document).on("click", ".add_label_value_group", function(e) {
        var last_item = $(this).parents("tr").find(".label-value-groups .label-value-group").last();
        var pre = last_item.attr("rel");
        var num = parseInt(pre)+1;
        var clone = $('<div>').append(last_item.clone()).html();

        clone = clone.replace('rel="'+pre+'"', 'rel="'+num+'"');
        clone = clone.replace('[options]['+pre+'][', '[options]['+num+'][');
        clone = clone.replace('[options]['+pre+'][', '[options]['+num+'][');
        clone = clone.replace('[options]['+pre+'][', '[options]['+num+'][');
        clone = clone.replace('[options]['+pre+'][', '[options]['+num+'][');
        clone = clone.replace('[options]['+pre+'][', '[options]['+num+'][');
        clone = clone.replace('[options]['+pre+'][', '[options]['+num+'][');
        clone = clone.replace('[options]['+pre+'][', '[options]['+num+'][');
        
        $(this).parents("tr").find(".label-value-groups").append(clone);
        $(this).parents("tr").find(".label-value-groups .label-value-group").last().find("input").val("");
        $(this).parents("tr").find(".label-value-groups .label-value-group").last().find(".help-block").remove();
        $(this).parents("tr").find(".label-value-groups .label-value-group").last().find(".form-group").removeClass("has-error");
    });
    
    // Validate confirm modal
    jQuery.validator.addMethod("deleteConfirm", function(value, element) {
        return value.toLowerCase() == "delete";
    }, LANG_DELETE_VALIDATE);
    $(".confirm-delete-form").each(function() {
        $(this).validate({
            rules: {
                delete: { deleteConfirm: true }
            }
        });
    });
    
    // Basic options
    $('.pickadate').pickadate({format: LANG_DATE_FORMAT});
    
    // Numberic input
    $(".numeric").numeric();
    
    // add segment condition
    $(document).on("click", ".add-segment-condition", function(e) {
        // ajax update custom sort
        $.ajax({
            method: "GET",
            url: $(this).attr('sample-url'),
        })
        .done(function( msg ) {
            var num = "0";
            
            if($('.segment-conditions-container .condition-line').length) {
                num = parseInt($('.segment-conditions-container .condition-line').last().attr("rel"))+1;
            }
            
            msg = msg.replace(/__index__/g, num);
            
            $('.segment-conditions-container').append(msg);
            
            var new_line = $('.segment-conditions-container .condition-line').last();
            new_line.find('select').select2();
        });
    });
    
    // add segment condition
    $(document).on("change", ".condition-line .operator-col select", function(e) {
        var op = $(this).val();
        
        if(op == 'blank' || op == 'not_blank') {
            $(this).parents(".condition-line").find('.value-col').css("visibility", "hidden");
        } else {
            $(this).parents(".condition-line").find('.value-col').css("visibility", "visible");
        }
    });
    
    // add segment condition
    $(document).on("click", "a.ajax_link", function(e) {
        e.preventDefault();
        $(".modal").modal('hide');
        
        var url = $(this).attr("href");
        var form = $(this).parents(".listing-form");
        
        $.ajax({
            method: "GET",
            url: url,
        })
        .done(function( msg ) {
            tableFilter($(".listing-form"));
            if(msg != '') {
                swal({
                    title: msg,
                    text: "",
                    confirmButtonColor: "#00695C",
                    type: "success",
                    allowOutsideClick: true,
                    confirmButtonText: LANG_OK,
                    customClass: "swl-success"
                });
            }
        });        
    });
    
    // Primary file input
	$(".file-styled-primary").uniform({
		wrapperClass: 'bg-warning',
		fileButtonHtml: '<i class="icon-plus2"></i>'
	});
    
    // Styled file input
    $(".file-styled").uniform({
        wrapperClass: 'bg-teal-400',
        fileButtonHtml: '<i class="icon-googleplus5"></i>'
    });
    
    // page preview action
    $(document).on("click", ".preview-page-button", function(e) {
        var url = $(this).attr('page-url');
        tinyMCE.triggerSave();
        var formData = new FormData($("#update-page")[0]);
        var frame = $('.preview_page_frame');
        var current_action = $("#update-page").attr("action");
        $("#update-page").attr('target', 'preview_page_frame');
        $("#update-page").attr('action', url);
        $("#update-page").submit();
        
        // after submit
        $("#update-page").removeAttr('target');
        $("#update-page").attr('action', current_action);

    });
    
    // Click to insert tag
    $(document).on("click", ".insert_tag_button", function(e) {
        var tag = $(this).attr("data-tag-name");
        
        if(!$(".plain_text_li").hasClass("active")) {
            tinymce.activeEditor.execCommand('mceInsertContent', false, tag);
        } else {
            $('textarea[name="plain"]').val($('textarea[name="plain"]').val()+tag);
        }
    });
    
    // Segments select box by list
    $(document).on("change", ".list_select_box select", function(e) {
        var url = $(this).parents('.list_select_box').attr("segments-url");
        var box = $("."+$(this).parents('.list_select_box').attr("target-box"));
        var id = $(this).val();
        
        if(id != '') {
            $.ajax({
                method: "GET",
                url: url,
                data: {
                    list_uid: id
                }
            })
            .done(function( msg ) {
                box.html(msg);
                
                // Select with search
                $('.select-search').select2();
            });
        } else {
            box.html('');
        }
    });
    
    // tab error
    $('.form-group.has-error').each(function() {
        var id = $(this).parents('.tab-pane').attr("id");
        $('a[href="#'+id+'"]').addClass('error');
    });
    
    // choose template
    $(document).on("click", ".choose-template-button", function() {
        var url = $(this).attr("data-url");
        
        $.ajax({
            method: "GET",
            url: url,
        })
        .done(function( msg ) {
            tinymce.activeEditor.execCommand('mceSetContent', false, msg);
            $(".modal").modal('hide');
        });
    });
    
    // Time picker
    if ($(".pickatime").length) {
        $(".pickatime").AnyTime_picker({
            format: "%H:%i"
        });
    }
    
    // Backend / Frontend
    updateCheckAccess();
    $(document).on('change', '#frontend_access', function() {
        updateCheckAccess();
    });
    $(document).on('change', '#backend_access', function() {
        updateCheckAccess();
    });
    
    // Setup chart
    $('.chart').each(function() {
        updateChart($(this));
    });
    
    // Campaign quickview dashboard
    $(document).on('change', '.dashboard-campaign-select', function() {
        dashboardQuickview($(this), $('.campaign-quickview-container'));
    });
    $('.dashboard-campaign-select').trigger("change");
    
    // List quickview dashboard
    $(document).on('change', '.dashboard-list-select', function() {
        dashboardQuickview($(this), $('.list-quickview-container'));
    });
    $('.dashboard-list-select').trigger("change");
    
    // scrollbar
    $('.scrollbar-box').mCustomScrollbar({theme:"minimal"});
    
    // Select2 ajax
    $(".select2-ajax").each(function() {
        var url = $(this).attr("data-url");
        $(this).select2({
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                  return {
                    q: params.term, // search term
                    page: params.page
                  };
                },
                processResults: function (data, params) {
                    // parse the results into the format expected by Select2
                    // since we are using custom formatting functions we do not need to
                    // alter the remote JSON data, except to indicate that infinite
                    // scrolling can be used
                    params.page = params.page || 1;
              
                    return {
                        results: data.items,
                        pagination: {
                            more: (params.page * 30) < data.total_count
                        }
                    };
                },
                cache: true
            },
            escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
            minimumInputLength: 0,
        });
    });
    
    // Campaign quickview dashboard
    $(document).on('click', '.top-quota-button', function() {
        var url = $(this).attr("data-url");
        $.ajax({
            method: "GET",
            url: url,
        })
        .done(function( msg ) {
            $('#quota_modal .modal-body').html(msg);
            $('a[data-target="#quota_modal"]').trigger("click");
        });        
    });
    
    // unlimited check
    $(document).on('change', '.unlimited-check input[type=checkbox]', function() {
        var box = $(this).parents(".boxing");
        
        if($(this).is(":checked")) {
            box.find("input[type=text]").val(-1);
            box.find("input[type=text]").addClass("text-trans");
            box.find("input[type=text]").attr("readonly", "readonly");
        } else {
            if(box.find("input[type=text]").val() == "-1") {
                box.find("input[type=text]").val(0);
            }
            box.find("input[type=text]").removeClass("text-trans");
            box.find("input[type=text]").removeAttr("readonly", "readonly");
        }
    });
    $('.unlimited-check input').trigger("change");
    
    // unlimited check
    $(document).on('click', 'ul.install-steps li:not(.enabled) a', function(e) {
        e.preventDefault();
    });
    
    // unlimited check
    $(document).on('click', '.copy-list-link', function(e) {
        var uid = $(this).attr("data-uid");
        var name = $(this).attr("data-name");
        
        $('input[name=copy_list_uid]').val(uid);
        $('input[name=copy_list_name]').val(name);
        $('a[data-target="#copy_list"]').trigger("click");
    });
    
    // Ajax copy list
    $(".ajax_copy_list_form").submit(function(e) {
        var url = $(this).attr("action");

        $.ajax({
            type: "POST",
            url: url,
            data: $(".ajax_copy_list_form").serialize(), // serializes the form's elements.
            success: function(msg)
            {
                tableFilter($(".listing-form"));
                if(msg != '') {
                    swal({
                        title: msg,
                        text: "",
                        confirmButtonColor: "#00695C",
                        type: "success",
                        allowOutsideClick: true,
                        confirmButtonText: LANG_OK,
                        customClass: "swl-success"
                    });
                }                
            }
        });
        
        $(".copy-list-close").trigger("click");
        e.preventDefault(); // avoid to execute the actual submit of the form.
    });
    
    // copy campaign
    $(document).on('click', '.copy-campaign-link', function(e) {
        var uid = $(this).attr("data-uid");
        var name = $(this).attr("data-name");
        
        $('input[name=copy_campaign_uid]').val(uid);
        $('input[name=copy_campaign_name]').val(name);
        $('a[data-target="#copy_campaign"]').trigger("click");
    });
    
    // Ajax copy campaign
    $(".ajax_copy_campaign_form").submit(function(e) {
        var url = $(this).attr("action");

        $.ajax({
            type: "POST",
            url: url,
            data: $(".ajax_copy_campaign_form").serialize(), // serializes the form's elements.
            success: function(msg)
            {
                tableFilter($(".listing-form"));
                if(msg != '') {
                    swal({
                        title: msg,
                        text: "",
                        confirmButtonColor: "#00695C",
                        type: "success",
                        allowOutsideClick: true,
                        confirmButtonText: LANG_OK,
                        customClass: "swl-success"
                    });
                }                
            }
        });
        
        $(".copy-campaign-close").trigger("click");
        e.preventDefault(); // avoid to execute the actual submit of the form.
    });
    
    // disble campaign step link
    $(document).on("click", ".campaign-steps li.disabled a", function(e) {
        e.preventDefault();
    });
    
    //embedded-options-form
    $(document).on("change", ".embedded-options-form input[type=text], .embedded-options-form textarea, .embedded-options-form input[name='required_fields'], .embedded-options-form input[name='javascript'], .embedded-options-form input[name='stylesheet']", function() {
        var url = $(this).parents("form").attr("action");
        
        updateEmbeddedForm($(this).parents("form"), url);
    });
    
    //embedded-options-form
    $(document).on("keyup", ".embedded-options-form input[type=text]", function() {
        var url = $(this).parents("form").attr("action");
        
        updateEmbeddedForm($(this).parents("form"), url);
    });
    
    $(".embedded-options-form input").trigger("change");
    
    
    // send a test email
    $(document).on('click', '.send-a-test-email-link', function(e) {
        var uid = $(this).attr("data-uid");
        
        $('input[name=send_test_email_campaign_uid]').val(uid);
        $('a[data-target="#send_a_test_email"]').trigger("click");
        
        e.preventDefault();
    });
    
    // Ajax send a test email
    $(".ajax_send_a_test_email_form").submit(function(e) {
        var url = $(this).attr("action");
        var form = $(".ajax_send_a_test_email_form");
        
        if(form.valid()) {
            form.addClass("loading");
            form.find("button[type='submit']").addClass("disabled");
            form.find("button[type='submit']").before('<i class="icon-spinner10 spinner position-left loading-icon"></i>');
            $.ajax({
                type: "POST",
                url: url,
                data: form.serialize(), // serializes the form's elements.
                success: function(data)
                {
                    data = JSON.parse(data);
                    swal({
                        title: '',
                        text: data.message,
                        confirmButtonColor: "#00695C",
                        type: data.status,
                        allowOutsideClick: true,
                        confirmButtonText: LANG_OK,
                        customClass: "swl-success"
                    });
                    
                    form.addClass("loading");
                    form.find("button[type='submit']").removeClass("disabled");
                    form.find('.loading-icon').remove();
                    $(".copy-campaign-close").trigger("click");
                }
            });

        }
        
        e.preventDefault(); // avoid to execute the actual submit of the form.
    });
});