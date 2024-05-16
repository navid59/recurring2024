jQuery(document).ready(function () {
    // Without Scrolling , Subscription - TMP 
    jQuery('#dtBasicExampleDeleted_REC').DataTable();
    jQuery('#planDtBasic').DataTable();
    jQuery('#reportDtBasic').DataTable({
        order: [[0, 'desc']],
    });

    jQuery('#subscriberInfoDtBasic').DataTable();
    jQuery('.dataTables_length').addClass('bs-select');

    // Infinit scrolling for Subscriptions 
	jQuery('#dtInfiniteScrollingExample').dataTable( {
		serverSide: true,
        ordering : false,
        searching : false,
        scrollY:        500,
        deferRender:    true,
        scroller: true,
        ajax: function ( data, callback ) {
            ntpPluginSetting = {
                action : 'getInfinitSubscribtion',
                start  : data.start,
                limit  : data.length
            };

            // Get Subscriptions by AJAX 
            jQuery.post(ajaxurl, ntpPluginSetting, function(response){
                // console.log(ntpPluginSetting.action + ' | start: ' + ntpPluginSetting.start + ' | limit : '+ ntpPluginSetting.limit);
                const responseObj = JSON.parse(response);
   
                callback( {
                    draw: data.draw,
                    data: responseObj.data,
                    recordsTotal: responseObj.recordsTotal,
                    recordsFiltered: responseObj.recordsTotal
                } );
            });
        },
        paging : true,
		columns: [
			{ "data": "First_Name" },
			{ "data": "Last_Name" },
			// { "data": "Email" },
			// { "data": "Tel" },
			{ "data": "UserID" },
			{ "data": "PlanTitle" },
			{ "data": "Status" },
			{ "data": "StartDate" },
			{ "data": "Action" },
		]
	} );


    // Infinit scrolling for Next Payment 
	jQuery('#dtInfiniteScrollingNextPayment').dataTable( {
		serverSide: true,
        ordering : false,
        searching : false,
        scrollY:        500,
        deferRender:    true,
        scroller: true,
        ajax: function ( data, callback ) {
            ntpPluginSetting = {
                action : 'getInfinitsNextPaymentList',
                start  : data.start,
                limit  : data.length
            };

            // Get Subscriptions by AJAX 
            jQuery.post(ajaxurl, ntpPluginSetting, function(response){
                console.log('Next Payment LIST ' + ntpPluginSetting.action + ' | start: ' + ntpPluginSetting.start + ' | limit : '+ ntpPluginSetting.limit);
                const responseObj = JSON.parse(response);
   
                callback( {
                    draw: data.draw,
                    data: responseObj.data,
                    recordsTotal: responseObj.recordsTotal,
                    recordsFiltered: responseObj.recordsTotal
                } );
            });
        },
        paging : true,
		columns: [
			{ "data": "First_Name" },
			{ "data": "Last_Name" },
			// { "data": "Email" },
			// { "data": "Tel" },
			{ "data": "UserID" },
			{ "data": "PlanTitle" },
			{ "data": "Status" },
			{ "data": "NextPaymentDate" },
			// { "data": "Action" },
		]
	} );
 
    // Hide Normal pagination 
    jQuery('#dtInfiniteScrollingExample_paginate').hide();
});


function subscriptionHistory(userId) {
    subscriptionHistoryData = {
        action : 'getSubscriptionHistory',
        userId : userId
    }
    jQuery.post(ajaxurl, subscriptionHistoryData, function(response){
        jsonResponse = JSON.parse(response);
        // console.log(jsonResponse);
        jQuery("#who").html(userId);
        const rows = jsonResponse.histories.map(history => {
            const tr = jQuery('<tr></tr>');
            tr.append(jQuery('<td></td>').text(history.CreatedAt.split(' ')[0]));
            tr.append(jQuery('<td></td>').text(history.Title + ' - ' + history.Amount + ' - ('+history.TransactionID+')'));
            // tr.append(jQuery('<td></td>').text(history.TransactionID));
            tr.append(jQuery('<td></td>').text(history.Comment));
            tr.append(jQuery('<td></td>').text(history.Status));
            return tr;
        });
        jQuery("#subscriberPaymentHistoryList").html(rows);
    });

    jQuery('#subscriberHistorytModal').modal('toggle');
    jQuery('#subscriberHistorytModal').modal('show');
}

function subscriptionDetails(userId) {
    subscriptionDetailData = {
        action : 'getSubscriptionDetail',
        userId : userId
    }
    jQuery.post(ajaxurl, subscriptionDetailData, function(response){
        jsonResponse = JSON.parse(response);
        console.log(jsonResponse);
        if(jsonResponse.code == "00") {
            jQuery("#SubscriberInfo_FirstaName").html(jsonResponse.data[0]['First_Name']);
            jQuery("#SubscriberInfo_LastName").html(jsonResponse.data[0]['Last_Name']);
            jQuery("#SubscriberInfo_UserId").html(jsonResponse.data[0]['UserID']);
            jQuery("#SubscriberInfo_Tel").html(jsonResponse.data[0]['Tel']);
            jQuery("#SubscriberInfo_Email").html(jsonResponse.data[0]['Email']);
            jQuery("#SubscriberInfo_Address").html(jsonResponse.data[0]['Address']);
            jQuery("#SubscriberInfo_City").html(jsonResponse.data[0]['City']);
            const rows = jsonResponse.plans.map(plan => {
                const tr = jQuery('<tr></tr>');
                tr.append(jQuery('<td></td>').text(plan.Title));
                tr.append(jQuery('<td></td>').text(plan.Amount));
                tr.append(jQuery('<td></td>').text(plan.StartDate.split(' ')[0]));
                tr.append(jQuery('<td></td>').text(plan.Status));
                // tr.append(jQuery('<td></td>').text(plan.LastPayment));
                tr.append(jQuery('<td></td>').html('<button type="button" class="btn btn-danger " title="Unsubscribe / Resubscribe" onclick="unsubscriptionAdminModal('+plan.Subscription_Id+',\''+plan.First_Name+' '+plan.Last_Name+'\',\''+plan.Title+'\')"><i class="fa fa-sliders"></i></button>'));
                tr.append(jQuery('<td></td>').html('<button type="button" class="btn btn-info" title="Next payment" onclick="subscriptionNextPayment('+plan.Subscription_Id+',\''+plan.First_Name+' '+plan.Last_Name+'\',\''+plan.Title+'\')"><i class="fa fa-credit-card"></i></button>'));                
                return tr;
            });
            jQuery("#subscriberPlanList").html(rows);
        } else {
            jQuery('#msgBlock').addClass('alert-warning');
            jQuery('#alertTitle').html('Error!');
            jQuery('#msgContent').html(jsonResponse.msg);
            jQuery('#msgBlock').addClass('show');
        }
    });

    jQuery('#subscriberInfotModal').modal('toggle');
    jQuery('#subscriberInfotModal').modal('show');
}


function unsubscriptionAdminModal(subscriptionId, subscriberName, planTitle) {
    jQuery('#unSubscriberName').html(subscriberName);
    jQuery('#unSubscriberPlanTitle').html(planTitle);

    jQuery('#textAlreadyUnsubscribed').hide();
    jQuery('#textAlreadySuspended').hide();
    jQuery('#textContinueUnsubscribe').hide();
    jQuery('#unsubscriptionByAdminButton').hide();
    jQuery('#resubscriptionByAdminButton').hide();
    jQuery('#unsubscriptionByAdminActionLoading').hide();
    jQuery('#unsubscribeAdminMsgBlock').removeClass('show');
    jQuery('#subscriberDetails').hide();
    jQuery("#unsubscriptionByAdminLoading").show();

    getNextPaymentData = {
        action : 'getNextPayment',
        subscriptionId: subscriptionId,
    }
    
    jQuery.post(ajaxurl, getNextPaymentData, function(response){
        jsonResponse = JSON.parse(response);
        console.log(jsonResponse);
        jQuery("#unsubscriptionByAdminLoading").hide();
        if(jsonResponse.status) {
            if(jsonResponse.data.isValid) {
                jQuery("#userCurrentStatus").html('Active');
                jQuery('#textContinueUnsubscribe').show();
                jQuery('#unsubscriptionByAdminButton').show();
            } else {
                jQuery("#userCurrentStatus").html('Inactive');
                if(jsonResponse.data.UserStatus == 3 ) {
                    jQuery('#textAlreadySuspended').show();
                    jQuery('#resubscriptionByAdminButton').show();
                } else if(jsonResponse.data.UserStatus == 2 ) {
                    jQuery('#textAlreadyUnsubscribed').show();
                }
                
            }
            jQuery("#userPaymentDate").html(jsonResponse.data.nextPayment);
            jQuery('#subscriberDetails').show();

            /** Unsubscription action */
            jQuery('#unsubscriptionByAdminButton').click(function (e){
                jQuery('#unsubscriptionByAdminActionLoading').show();
                jQuery('#unsubscriptionByAdminButton').hide();

                unsubscribeData = {
                    action : 'adminUnsubscription',
                    SubscriptionId : getNextPaymentData.subscriptionId,
                }

                jQuery.post(ajaxurl, unsubscribeData, function(response){
                    jsonResponse = JSON.parse(response);
                    console.log(jsonResponse);
                    if(jsonResponse.status) {
                        jQuery('#unsubscriptionByAdminActionLoading').hide();
                        jQuery('#unsubscribeAdminMsgBlock').addClass('alert-success');
                        jQuery('#unsubscribeAdminAlertTitle').html('Success!');
                        jQuery('#unsubscribeAdminMsgContent').html(jsonResponse.msg);
                        jQuery('#unsubscribeAdminMsgBlock').addClass('show');
                    } else {
                        jQuery('#unsubscriptionByAdminActionLoading').hide();
                        jQuery('#unsubscribeAdminMsgBlock').addClass('alert-warning');
                        jQuery('#unsubscribeAdminAlertTitle').html('Error!');
                        jQuery('#unsubscribeAdminMsgContent').html(jsonResponse.msg);
                        jQuery('#unsubscribeAdminMsgBlock').addClass('show');
                    }
                });
            });

            /** Resubscription action */
            jQuery('#resubscriptionByAdminButton').click(function (e){
                jQuery('#unsubscriptionByAdminActionLoading').show();
                jQuery('#resubscriptionByAdminButton').hide();

                unsubscribeData = {
                    action : 'adminResubscription',
                    SubscriptionId : getNextPaymentData.subscriptionId,
                }

                jQuery.post(ajaxurl, unsubscribeData, function(response){
                    jsonResponse = JSON.parse(response);
                    // console.log(jsonResponse);
                    if(jsonResponse.status) {
                        jQuery('#unsubscriptionByAdminActionLoading').hide();
                        jQuery('#unsubscribeAdminMsgBlock').addClass('alert-success');
                        jQuery('#unsubscribeAdminAlertTitle').html('Success!');
                        jQuery('#unsubscribeAdminMsgContent').html(jsonResponse.msg);
                        jQuery('#unsubscribeAdminMsgBlock').addClass('show');
                    } else {
                        jQuery('#resubscriptionByAdminActionLoading').hide();
                        jQuery('#unsubscribeAdminMsgBlock').addClass('alert-warning');
                        jQuery('#unsubscribeAdminAlertTitle').html('Error!');
                        jQuery('#unsubscribeAdminMsgContent').html(jsonResponse.msg);
                        jQuery('#unsubscribeAdminMsgBlock').addClass('show');
                    }
                });
            });

        } else {
            jQuery('#msgBlock').addClass('alert-warning');
            jQuery('#alertTitle').html('Error!');
            jQuery('#msgContent').html(jsonResponse.msg);
            jQuery('#msgBlock').addClass('show');
        }
    });

    // Refresh page after close Unsubscribe Modal
    jQuery('#unsubscribeModal').on('hidden.bs.modal', function() {
        window.location.reload();
    });

    jQuery('#unsubscribeModal').modal('toggle');
    jQuery('#unsubscribeModal').modal('show');
}


function subscriptionNextPayment(subscriptionId, subscriberName, planTitle) {
    jQuery('#subscriberName').html(subscriberName);
    jQuery('#thePlanTitle').html(planTitle);


    getNextPaymentData = {
        action : 'getNextPayment',
        subscriptionId: subscriptionId,
    }
    jQuery.post(ajaxurl, getNextPaymentData, function(response){
        jsonResponse = JSON.parse(response);
        console.log(jsonResponse);
        if(jsonResponse.status) {
            jQuery("#nextPaymentByAdminLoading").hide();
            if(jsonResponse.data.isValid) {
                jQuery("#nextPaymentStatus").html('Active');
            } else {
                jQuery("#nextPaymentStatus").html('Inactive');
            }
            
            jQuery("#nextPaymentDate").html(jsonResponse.data.nextPayment);
        } else {
            jQuery("#nextPaymentByAdminLoading").hide();
            jQuery('#msgBlock').addClass('alert-warning');
            jQuery('#alertTitle').html('Error!');
            jQuery('#msgContent').html(jsonResponse.msg);
            jQuery('#msgBlock').addClass('show');
        }
    });

    jQuery('#nextPaymentModal').modal('toggle');
    jQuery('#nextPaymentModal').modal('show');
}

function delPlan(planId) {
    jQuery('#msgDelete').html('Are you sure you want to delete the plan ?');
    jQuery('#planId').val(planId);
    jQuery('#deletePlanModal').modal('toggle');
    jQuery('#deletePlanModal').modal('show');

    // Submit the form for Update Plan
    jQuery('#recurring-delete-plan-form').on('submit', doDeletePlan);
    
    return false;
}


function doDeletePlan(e) {
    e.preventDefault(); // to stop Submit Event
    var planId = jQuery("#planId").val();
    if (jQuery("#unsubscribe").prop("checked")) {
        var unsubscribe = true;
    } else {
        var unsubscribe = false;
    }
    
    if (jQuery("#conditions").prop("checked")) {
        var acceptedConditions = true;
    } else {
        var acceptedConditions = false;
    }

    data = {
        action : 'delPlan',
        planId : planId,
        unsubscribe : unsubscribe,
    };

    if(acceptedConditions) {
        jQuery('#delPlanLoading').show();
        jQuery('#deletePlan').prop('disabled', true);
        jQuery.post(ajaxurl, data, function(response){
            if(response.status) {
                jQuery('#delPlanLoading').hide();
                jQuery('#deletePlan').hide();

                jQuery('#msgBlock').addClass('alert-success');
                jQuery('#alertTitle').html('Congratulation!');
                jQuery('#msgContent').html(response.msg);
                jQuery('#msgBlock').addClass('show');

                // Refresh page after close Modal
                jQuery('#deletePlanModal').on('hidden.bs.modal', function() {
                    window.location.reload();
                });
            } else {
                jQuery('#delPlanLoading').hide();
                jQuery('#deletePlan').prop('disabled', false);

                jQuery('#msgBlock').addClass('alert-warning');
                jQuery('#alertTitle').html('Error!');
                jQuery('#msgContent').html(response.msg);
                jQuery('#msgBlock').addClass('show');
            }
        });
    } else {
        
        jQuery('#msgBlock').addClass('alert-warning');
        jQuery('#alertTitle').html('Error!');
        jQuery('#msgContent').html('You should to accept terms & conditions!');
        jQuery('#msgBlock').addClass('show');
    }
}


function editPlan(planId) {  
    jQuery("#editPlanId").val(planId);
    
    getPlanData = {
        action : 'getPlanInfo',
        planIdentity: planId,
    }
    jQuery.post(ajaxurl, getPlanData, function(response){
        jsonResponse = JSON.parse(response);
        // console.log(jsonResponse);
        if(jsonResponse.status) {
            jQuery("#editPlanTitile").val(jsonResponse.data.Title);
            jQuery("#editPlanDescription").val(jsonResponse.data.Description);
            jQuery("#editRecurrenceType").val(jsonResponse.data.RecurrenceType);
            jQuery("#editFrequencyType").val(jsonResponse.data.Frequency.Type);
            jQuery("#editFrequencyValue").val(jsonResponse.data.Frequency.Value);
            jQuery("#editAmount").val(jsonResponse.data.Amount);
            jQuery("#editCurrency").val(jsonResponse.data.Currency);
            jQuery("#editGracePeriod").val(jsonResponse.data.GracePeriod);
            
            if(jsonResponse.data.InitialPayment == 'true') {
                jQuery('#editInitialPayment').prop( "checked", true );
            }else {
                jQuery('#editInitialPayment').prop( "checked", false );
            }
        } else {
            // Alert &  exit
            jQuery('#editMsgBlock').addClass('alert-warning');
            jQuery('#editAlertTitle').html('Error!');
            jQuery('#editMsgContent').html('Something is wrong!. Please check your configuration');
            jQuery('#editFormAttributes').hide();
            jQuery('#editMsgBlock').addClass('show');
        }
    });


    jQuery('#editPlanModal').modal('toggle');
    jQuery('#editPlanModal').modal('show');

    // Submit the form for Update Plan
    jQuery('#recurring-edit-plan-form').on('submit', doUpdatePlan);
    
    return false;
}

function doUpdatePlan(e) {
    e.preventDefault(); // to stop Submit Event
    var planId = jQuery("#editPlanId").val();
    var planTitile = jQuery("#editPlanTitile").val();
    var planDescription = jQuery("#editPlanDescription").val();
    var RecurrenceType = jQuery("#editRecurrenceType").val();
    var FrequencyType = jQuery("#editFrequencyType").val();
    var FrequencyValue = jQuery("#editFrequencyValue").val();
    var Amount = jQuery("#editAmount").val();
    var Currency = jQuery("#editCurrency").val();
    var GracePeriod = jQuery("#editGracePeriod").val();
    if (jQuery("#editInitialPayment").prop("checked")) {
        var InitialPayment = true;
    } else {
        var InitialPayment = false;
    }
    if (jQuery("#editConditions").prop("checked")) {
        var acceptedConditions = true;
    } else {
        var acceptedConditions = false;
    }

    data = {
        action : 'editPlan',
        planId : planId,
        planTitile : planTitile,
        planDescription : planDescription,
        RecurrenceType : RecurrenceType,
        FrequencyType : FrequencyType,
        FrequencyValue : FrequencyValue,
        Amount : Amount,
        Currency : Currency,
        GracePeriod : GracePeriod,
        InitialPayment : InitialPayment,
        TermAndConditionAccepted : acceptedConditions,
    };
    
    if(acceptedConditions) {
        jQuery('#editPlanLoading').show();
        jQuery('#editPlan').prop('disabled', true);
        jQuery.post(ajaxurl, data, function(response){
            if(response.status) {
                jQuery('#editPlanLoading').hide();
                
                jQuery('#editMsgBlock').addClass('alert-success');
                jQuery('#editAlertTitle').html('Congratulation!');
                jQuery('#editMsgContent').html(response.msg);
                jQuery('#editMsgBlock').addClass('show');

                // Disable after success update
                jQuery("#editPlanTitile").prop('readonly', true);
                jQuery("#editPlanDescription").prop('readonly', true);
                jQuery("#editRecurrenceType").attr("disabled", true); 
                jQuery("#editFrequencyType").attr("disabled", true); 
                jQuery("#editFrequencyValue").prop('readonly', true);
                jQuery("#editAmount").prop('readonly', true);
                jQuery("#editCurrency").attr("disabled", true); 
                jQuery("#editGracePeriod").prop('readonly', true);
                jQuery("#editInitialPayment").prop('readonly', true);
                jQuery("#editConditions").prop('readonly', true);
                
                // Remove submit button after success
                jQuery("#editPlan").hide();

                // Refresh page after close Modal
                jQuery('#editPlanModal').on('hidden.bs.modal', function() {
                    window.location.reload();
                });
            }else {
                jQuery('#editPlanLoading').hide();
                jQuery('#editPlan').prop('disabled', false);
                jQuery('#editMsgBlock').addClass('alert-warning');
                jQuery('#editAlertTitle').html('Error!');
                jQuery('#editMsgContent').html(response.msg);
                jQuery('#editMsgBlock').addClass('show'); 
            }
        });
    } else {
        jQuery('#editMsgBlock').addClass('alert-warning');
        jQuery('#editAlertTitle').html('Error!');
        jQuery('#editMsgContent').html('You should to accept terms & conditions!');
        jQuery('#editMsgBlock').addClass('show');
    }

};

function copyPlan(planId, planTitile) {
    var shortCode = '[NTP-Recurring planId='+ planId +' button="Subscribe" title="'+ planTitile +'"]';
    jQuery('#shortcode').html(shortCode);
    jQuery('#copyShortCodeModal').modal('toggle');
    jQuery('#copyShortCodeModal').modal('show');
    navigator.clipboard.writeText(shortCode);
}

function addPlanInAction() {
    var planTitile = jQuery("#planTitile").val();
    var planDescription = jQuery("#planDescription").val();
    var RecurrenceType = jQuery("#RecurrenceType").val();
    var FrequencyType = jQuery("#FrequencyType").val();
    var FrequencyValue = jQuery("#FrequencyValue").val();
    var Amount = jQuery("#Amount").val();
    var Currency = jQuery("#Currency").val();
    var GracePeriod = jQuery("#GracePeriod").val();
    if (jQuery("#InitialPayment").prop("checked")) {
        var InitialPayment = true;
    } else {
        var InitialPayment = false;
    }
                
    data = {
        action : 'addPlan',
        planTitile : planTitile,
        planDescription : planDescription,
        RecurrenceType : RecurrenceType,
        FrequencyType : FrequencyType,
        FrequencyValue : FrequencyValue,
        Amount : Amount,
        Currency : Currency,
        GracePeriod : GracePeriod,
        InitialPayment : InitialPayment,
    };
   
    jQuery.post(ajaxurl, data, function(response){
        if(response.status) {
            jQuery('#msgBlock').addClass('alert-success');
            jQuery('#alertTitle').html('Congratulation!');
            jQuery('#msgContent').html(response.msg);
            jQuery('#msgBlock').addClass('show');

            // Reset form and hide it
            document.getElementById("recurring-plan-form").reset();
            jQuery('#recurring-plan-form').hide();
            jQuery('#addNewPlan').addClass('show');

        }else {
            jQuery('#msgBlock').addClass('alert-warning');
            jQuery('#alertTitle').html('Error!');
            jQuery('#msgContent').html(response.msg);
            jQuery('#msgBlock').addClass('show');
        }
    });
}

jQuery("#recurring-plan-form").submit(function (e) {

        jQuery('#addPlanLoading').show();
        jQuery('#addPlan').prop('disabled', true);

        subscriptionDetailData = {
            action : 'verifyCredentialData'
        }
    
        jQuery.post(ajaxurl, subscriptionDetailData, function(response){
            jsonResponse = JSON.parse(response);
            console.log(jsonResponse);
            if(jsonResponse.code == "00") {
                 /**
                 * Verify Credential data With success!!!,... 
                 * Will send plan info to API
                 */ 
                    addPlanInAction();    
            } else {
                /**
                 * Verify Credential data FAILED!!!,... 
                 * Will NOT send plan info to API
                 */
                 jQuery('#addPlanLoading').hide();
                 jQuery('#addPlan').prop('disabled', false);
                 toastr.error('Invalid credential data. Please first set the corect API key & Signature in Recurring v1->Setting section, and then try again!', 'Error!');
            }
        });                
    // important to Return
    return false;
});

function certificateNotifyHandle(chosen_file,type,elm){
    var fsize = chosen_file.size,
        fname = chosen_file.name,
        fextension = fname.substring(fname.lastIndexOf('.')+1);
    if (fextension != type){
        toastr.error('This type of files are not allowed! just files with "cer" extensions as PUBLIC KEY and "key" extensions as "PRIVATE KEY" are accepted', 'Error!');
        elm.value = "";
        return false;
    } else if(fsize > 3145728 || fsize <= 0) {
        toastr.error('Please choose a file with a valid size', 'Error!');
        elm.value = "";
        return false;
    } else {
        toastr.success('File is verified', 'success!');
        return true;
    }
}

jQuery('#netopia_recurring_general_public_key').on('change', function () {
    let fileVerified = certificateNotifyHandle(jQuery(this)[0].files[0],'cer',this);
    if(fileVerified) {
        file_data = jQuery(this)[0].files[0];
        form_data = new FormData();
        form_data.append('netopia_recurring_general_public_key', file_data);
        form_data.append('action', 'uploadKey');
        
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            contentType: false,
            processData: false,
            data: form_data,
            success: function (response) {
                jQuery(this).val('');
                jQuery('#netopia_recurring_general_public_key_file_name').val(file_data.name);
                jQuery('#description_netopia_recurring_general_public_key').html('File uploaded successfully.');
            }
        });
    }
});

function CopyToClipboard(id)
{
    var id = 'planShortCode';
    var r = document.createRange();
    r.selectNode(document.getElementById(id));
    window.getSelection().removeAllRanges();
    window.getSelection().addRange(r);
    document.execCommand('copy');
    window.getSelection().removeAllRanges();
}