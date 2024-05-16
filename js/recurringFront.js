jQuery(document).ready(function () {
    jQuery('#frontAccountMyPaymentHistory').click(function() {
        jQuery('#frontAccountMysubscription').removeClass('active');
        jQuery('#frontAccountMyPaymentHistory').addClass('active');
        jQuery('#frontAccountDetails').removeClass('active');
        // jQuery('#frontCardInfoUpdate').removeClass('active');
        jQuery('#frontAccountLogout').removeClass('active');
        
        data = {
            action : 'getMyHistory'
        }
    
        jQuery.ajax({
            url: frontAjax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function( response ){
                if(response.status) {      
                    jQuery('#ntpAccountBody').html(response.data);
 
                    const rows = response.histories.map(history => {
                        const tr = jQuery('<tr></tr>');
                        tr.append(jQuery('<td></td>').text(history.CreatedAt.split(' ')[0]));
                        tr.append(jQuery('<td></td>').text(history.Title + ' - ' + history.Amount));
                        // tr.append(jQuery('<td></td>').text(history.TransactionID));
                        // tr.append(jQuery('<td></td>').text(history.Comment));
                        tr.append(jQuery('<td></td>').text(history.Status));
                        return tr;
                    });
                    jQuery("#mySubscriberPaymentHistoryList").html(rows);               
                } else {
                    jQuery('#ntpAccountBody').html(response.msg);
                }
            },
            error: function( error ){
                jQuery('#ntpAccountBody').html(response.msg);
            }
        });
    });

    jQuery('#frontAccountMysubscription').click(function() {
        jQuery('#frontAccountMysubscription').addClass('active');
        jQuery('#frontAccountMyPaymentHistory').removeClass('active');
        jQuery('#frontAccountDetails').removeClass('active');
        // jQuery('#frontCardInfoUpdate').removeClass('active');
        jQuery('#frontAccountLogout').removeClass('active');

        data = {
            action : 'getMySubscriptions'
        }
    
        jQuery.ajax({
            url: frontAjax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function( response ){
                if(response.status) {                    
                    jQuery('#ntpAccountBody').html(response.data);
                    
                } else {
                    jQuery('#ntpAccountBody').html(response.msg);
                }
            },
            error: function( error ){
                jQuery('#ntpAccountBody').html(response.msg);
            }
        });
    });


    jQuery('#frontAccountLogout').click(function() {
        jQuery('#frontAccountMysubscription').removeClass('active');
        jQuery('#frontAccountMyPaymentHistory').removeClass('active');
        jQuery('#frontAccountDetails').removeClass('active');
        // jQuery('#frontCardInfoUpdate').removeClass('active');
        jQuery('#frontAccountLogout').addClass('active');

        data = {
            action : 'logoutAccount'
        };
    
        jQuery.ajax({
            url: frontAjax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function( response ){
                if(response.status) {
                    location.replace(response.redirectUrl);                  
                } else {
                    //
                }
            },
            error: function( error ){
                //
            }
        });
    });


    jQuery('#frontAccountDetails').click(function() {
        jQuery('#frontAccountMysubscription').removeClass('active');
        jQuery('#frontAccountMyPaymentHistory').removeClass('active');
        jQuery('#frontAccountDetails').addClass('active');
        // jQuery('#frontCardInfoUpdate').removeClass('active');
        jQuery('#frontAccountLogout').removeClass('active');
        
        data = {
            action : 'getMyAccountDetails'
        }
    
        jQuery.ajax({
            url: frontAjax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function( response ){
                if(response.status) {                    
                    jQuery('#ntpAccountBody').html(response.data);
                    
                } else {
                    jQuery('#ntpAccountBody').html(response.msg);
                }
            },
            error: function( error ){
                jQuery('#ntpAccountBody').html(response.msg);
            }
        });
    });

    /**
     * To update card data
     * Not implimented complitlly 
     * Do not remove
     */
    // jQuery('#frontCardInfoUpdate').click(function() {
    //     jQuery('#frontAccountMysubscription').removeClass('active');
    //     jQuery('#frontAccountMyPaymentHistory').removeClass('active');
    //     jQuery('#frontAccountDetails').removeClass('active');
    //     jQuery('#frontCardInfoUpdate').addClass('active');
    //     jQuery('#frontAccountLogout').removeClass('active');
        
    //     data = {
    //         action : 'getMyCardDetails'
    //     }
    
    //     jQuery.ajax({
    //         url: frontAjax.ajax_url,
    //         type: 'POST',
    //         dataType: 'json',
    //         data: data,
    //         success: function( response ){
    //             if(response.status) {                    
    //                 jQuery('#ntpAccountBody').html(response.data);
                    
    //             } else {
    //                 jQuery('#ntpAccountBody').html(response.msg);
    //             }
    //         },
    //         error: function( error ){
    //             jQuery('#ntpAccountBody').html(response.msg);
    //         }
    //     });
    // });

    // Ltrim  Card Number
    jQuery('input[name=cc-number]').change(function() {
        const str = this.value;
        this.value = str.trim();
    });

    /**
     * To get card expire Date
     * Find month and year of Exp Date
     */

     var liveValidateDetails = function(ntpRecurringFormID) { 
     var thePlanID = ntpRecurringFormID.split(/subscription-form(.*)/)[1];
        
     var live_expiry = jQuery('.cc-exp_recurring_'+thePlanID).payment('cardExpiryVal');
     var live_validateExpiry = jQuery.payment.validateCardExpiry(live_expiry["month"], live_expiry["year"]);
     
     
        if (live_validateExpiry) {
            jQuery('#cc-expiration-month_'+thePlanID).val(live_expiry["month"]);
            jQuery('#cc-expiration-year_'+thePlanID).val(live_expiry["year"]);

            // jQuery('.ntpRecurringCheckout').prop('disabled', false);
            jQuery('#addSubscriptionButton'+thePlanID).prop('disabled', false);
            

            jQuery('.cc-exp_recurring_'+thePlanID).addClass('identified');
            jQuery('.cc-exp__ouputValid').text('Yes');
        } else {
            // To clear Month & Year
            jQuery('#cc-expiration-month_'+thePlanID).val("");
            jQuery('#cc-expiration-year_'+thePlanID).val("");

            // jQuery('.ntpRecurringCheckout').prop('disabled', true);
            jQuery('#addSubscriptionButton'+thePlanID).prop('disabled', true);

            jQuery('.cc-exp_recurring_'+thePlanID).removeClass('identified');
            jQuery('.cc-exp__ouputValid').text('No');
        }
    }

     jQuery('.cc-exp').payment('formatCardExpiry');
     jQuery('.cc-exp').bind('change exp keyup', function() {
        liveValidateDetails(this.form.id);
      });
    
    jQuery('.add-subscription-form').on('submit', addSubscription);
    jQuery('.unsubscription-form').on('submit', unsubscription);
    /** Verify Auth */
    jQuery('.verify-action-botton').on('click', VerifyAuthAction);

    /**To move Modal for IOS */
    jQuery('.recurringModal').appendTo('body');
});


function unsubscription(e) {
    e.preventDefault(); // to stop Submit Event
    var form = jQuery(this);
    var formId = form.attr('id');

    var SubscriptionId = jQuery('#'+formId).find('input[name=Subscription_Id]').val();  
    var Id = jQuery('#'+formId).find('input[name=Id]').val();
    var planId = jQuery('#'+formId).find('input[name=planId]').val();

    jQuery('#loading'+SubscriptionId).addClass('show');
    jQuery('#unsubscriptionButton'+planId).hide();
    
    data = {
        action : 'unsubscription',
        Id : Id,
        SubscriptionId : SubscriptionId,
    }

    jQuery.ajax({
        url: frontAjax.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function( response ){
            if(response.status) {
                jQuery('#msgBlock'+SubscriptionId).addClass('alert-success');
                jQuery('#alertTitle'+SubscriptionId).html('Congratulation!');
                jQuery('#msgContent'+SubscriptionId).html(response.msg);
                jQuery('#msgBlock'+SubscriptionId).addClass('show');
                jQuery('#loading'+SubscriptionId).removeClass('show');
                jQuery('#'+formId).hide();               
                

                // Close Modal Automaticly aftre 10 Secound
                setTimeout(function() { jQuery('.unsubscriptionRecurringModal').modal('hide');}, 10000);

                // Refresh page after close Modal
                jQuery('.unsubscriptionRecurringModal').on('hidden.bs.modal', function() {
                    window.location.reload();
                });                
            } else {
                jQuery('#msgBlock'+SubscriptionId).addClass('alert-warning');
                jQuery('#alertTitle'+SubscriptionId).html('Error!');
                jQuery('#msgContent'+SubscriptionId).html(response.msg);
                jQuery('#msgBlock'+SubscriptionId).addClass('show');
                jQuery('#unsubscriptionButton'+planId).show();
                jQuery('#loading'+SubscriptionId).removeClass('show');
            }
        },
        error: function( error ){
            jQuery('#msgBlock'+SubscriptionId).addClass('alert-warning');
            jQuery('#alertTitle'+SubscriptionId).html('Error!');
            jQuery('#msgContent'+SubscriptionId).html(response.msg);
            jQuery('#msgBlock'+SubscriptionId).addClass('show');
            jQuery('#unsubscriptionButton'+planId).show();
            jQuery('#loading'+SubscriptionId).removeClass('show');
        }
    });
}

function unsubscriptionMyAccount() {
    jQuery('#loading').addClass('show');
    jQuery('#unsubscriptionButton').hide();

    var SubscriptionId = jQuery("#Subscription_Id").val();
    var Id = jQuery("#Id").val();

    data = {
        action : 'unsubscription',
        Id : Id,
        SubscriptionId : SubscriptionId,
    }

    jQuery.ajax({
        url: frontAjax.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function( response ){
            if(response.status) {
                jQuery('#myAccountMsgBlock').addClass('alert-success');
                jQuery('#myAccountAlertTitle').html('Congratulation!');
                jQuery('#myAccountMsgContent').html(response.msg);
                jQuery('#myAccountMsgBlock').addClass('show');
                jQuery('#loading').removeClass('show');
                jQuery('#unsubscription-form').hide();               
                
                // Close Modal Automaticly aftre 10 Secound
                setTimeout(function() { jQuery('#unsubscriptionMyAccountModal').modal('hide');}, 10000);

                // Refresh page after close Modal
                jQuery('#unsubscriptionMyAccountModal').on('hidden.bs.modal', function() {
                    window.location.reload();
                });                
            } else {
                jQuery('#myAccountMsgBlock').addClass('alert-warning');
                jQuery('#myAccountAlertTitle').html('Error!');
                jQuery('#myAccountMsgContent').html(response.msg);
                jQuery('#myAccountMsgBlock').addClass('show');
                jQuery('#unsubscriptionButton').show();
                jQuery('#loading').removeClass('show');
            }
        },
        error: function( error ){
            jQuery('#myAccountMsgBlock').addClass('alert-warning');
            jQuery('#myAccountAlertTitle').html('Error!');
            jQuery('#myAccountMsgContent').html(response.msg);
            jQuery('#myAccountMsgBlock').addClass('show');
            jQuery('#unsubscriptionButton').show();
            jQuery('#loading').removeClass('show');
        }
    });
}

function addSubscription(e) {   
    e.preventDefault(); // to stop Submit Event
    var form = jQuery(this);
    var formId = form.attr('id');

    var PlanID = jQuery('#'+formId).find('input[name=planID]').val();
    var UserID = jQuery('#'+formId).find('input[name=username]').val();
    var Pass = jQuery('#'+formId).find('input[name=password]').val();
    var Name = jQuery('#'+formId).find('input[name=firstName]').val();
    var LastName = jQuery('#'+formId).find('input[name=lastName]').val();
    var Email = jQuery('#'+formId).find('input[name=email]').val();
    var Address  = jQuery('#'+formId).find('input[name=address]').val();
    var City = jQuery('#'+formId).find('select[name=state]').val();
    var Tel = jQuery('#'+formId).find('input[name=tel]').val();
    

    var PlanId = PlanID;//jQuery("#PlanId").val();
    var StartDate =  jQuery('#'+formId).find('input[name=StartDate]').val();
    var EndDate = jQuery('#'+formId).find('input[name=EndDate]').val();


    var Account = jQuery('#'+formId).find('input[name=cc-number]').val();
    var ExpMonth = jQuery('#'+formId).find('input[name=cc-expiration-month]').val();
    var ExpYear = jQuery('#'+formId).find('input[name=cc-expiration-year]').val();
    var SecretCode = jQuery('#'+formId).find('input[name=cc-cvv]').val();

    var ThreeDS = sendClientBrowserInfo();

    var BackUrl = jQuery('#backUrl'+PlanID).val(); // will be contain current page + ID of plan
    
    jQuery('#loading'+PlanID).addClass('show');
    jQuery('#addSubscriptionButton'+PlanID).hide();


    if(!luhn_validate(Account)) {
        jQuery('#msgBlock'+PlanID).addClass('alert-warning');
        jQuery('#alertTitle'+PlanID).html('Error!');
        jQuery('#msgContent'+PlanID).html('Your card is not a valid card, please use a valid one!');
        jQuery('#msgBlock'+PlanID).addClass('show');
        jQuery('#addSubscriptionButton'+PlanID).show();
        jQuery('#loading'+PlanID).removeClass('show');
    } else {
        data = {
            action : 'addNewSubscription',
            PlanID : PlanID,
            UserID : UserID,
            Pass : Pass,
            Name : Name,
            LastName : LastName,
            Email : Email,
            Address : Address,
            City : City,
            Tel : Tel,
            PlanId : PlanId,
            StartDate : StartDate,
            EndDate : EndDate,
            Account : Account,
            ExpMonth : ExpMonth,
            ExpYear : ExpYear,
            SecretCode : SecretCode,
            ThreeDS : ThreeDS,
            BackUrl : BackUrl,
        };
        
        jQuery.ajax({
            url: frontAjax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function( response ){
                if(response.status) {
                    jQuery('#msgBlock'+PlanID).addClass('alert-success');
                    jQuery('#alertTitle'+PlanID).html('Congratulation!');
                    jQuery('#msgContent'+PlanID).html(response.msg);
                    jQuery('#msgBlock'+PlanID).addClass('show');
                    jQuery('#loading'+PlanID).removeClass('show');
    
                    /** Make form Read only on success */
                    jQuery('#'+formId).find('input[name=planID]').prop('readonly', true);
                    jQuery('#'+formId).find('input[name=username]').prop('readonly', true);
                    jQuery('#'+formId).find('input[name=firstName]').prop('readonly', true);
                    jQuery('#'+formId).find('input[name=lastName]').prop('readonly', true);
                    jQuery('#'+formId).find('input[name=email]').prop('readonly', true);
                    jQuery('#'+formId).find('input[name=address]').prop('readonly', true);
                    jQuery('#'+formId).find('input[name=country]').attr("disabled", true); 
                    jQuery('#'+formId).find('input[name=state]').attr("disabled", true); 
                    jQuery('#'+formId).find('input[name=tel]').prop('readonly', true);
                    jQuery('#'+formId).find('input[name=PlanId]').prop('readonly', true);
                    jQuery('#'+formId).find('input[name=StartDate]').prop('readonly', true);
                    jQuery('#'+formId).find('input[name=EndDate]').prop('readonly', true);
                    jQuery('#'+formId).find('input[name=cc-name]').prop('readonly', true);
                    jQuery('#'+formId).find('input[name=cc-number]').prop('readonly', true);
                    jQuery('#'+formId).find('input[name=cc-expiration-month]').prop('readonly', true);
                    jQuery('#'+formId).find('input[name=cc-expiration-year]').prop('readonly', true);
                    jQuery('#'+formId).find('input[name=cc-cvv]').prop('readonly', true);
    
                    // Close Modal Automaticly aftre 10 Secound
                    setTimeout(function() { jQuery('.recurringModal').modal('hide');}, 10000);

                    // Refresh page after close Modal
                    jQuery('.recurringModal').on('hidden.bs.modal', function() {
                        window.location.reload();
                    });
                } else {
                    console.log(response);
                    /**
                     * If response.detail is not exist
                     * perhapse an error happend, as example could be existan user , ...
                     * So me have status & msg
                     */

                     if (typeof response.detail === 'undefined') {
                        jQuery('#msgBlock'+PlanID).addClass('alert-warning');
                        jQuery('#alertTitle'+PlanID).html('Error!');
                        jQuery('#msgContent'+PlanID).html(response.msg);
                        jQuery('#msgBlock'+PlanID).addClass('show');
                        jQuery('#addSubscriptionButton'+PlanID).show();
                        jQuery('#loading'+PlanID).removeClass('show');
                        return false;
                    }

                    /**
                     * If has some unexpected Error
                     * And stop / return  
                     */
                    if (response.detail === null) {
                        jQuery('#msgBlock'+PlanID).addClass('alert-warning');
                        jQuery('#alertTitle'+PlanID).html('Error!');
                        jQuery('#msgContent'+PlanID).html(response.msg);
                        jQuery('#msgBlock'+PlanID).addClass('show');
                        jQuery('#addSubscriptionButton'+PlanID).show();
                        jQuery('#loading'+PlanID).removeClass('show');
                        return false;
                    } 

                    /**
                     * If need auth 
                     */
                    if(response.detail.PaymentCode == "100") {
                        let actionUrl = response.detail.PaymentDetails.ThreeDS.url;
                        let authenticationToken   = response.detail.PaymentDetails.AuthenticationToken;
                        let ntpID   = response.detail.PaymentDetails.NtpID;

                        // form data 
                        var formDataObj = response.detail.PaymentDetails.ThreeDS.formData;

                        var dynamicForm = document.createElement("form");
                        dynamicForm.setAttribute("method", "post");
                        dynamicForm.setAttribute("name", "authorizeForm"+PlanID);
                        dynamicForm.setAttribute("action", actionUrl);

                        // create input elements for dynamic form
                        jQuery.each(formDataObj, function(propName, propVal) {
                            var tmpVar = document.createElement("input");
                                tmpVar.setAttribute("type", "hidden");
                                tmpVar.setAttribute("name", propName);
                                tmpVar.setAttribute("value", propVal);
                                dynamicForm.appendChild(tmpVar);
                        });

                        // create a submit button
                        var s = document.createElement("input");
                            s.setAttribute("type", "submit");
                            s.setAttribute("value", "Redirecting to your bank");
                            s.setAttribute("id", "authorizeFormSubmmit"+PlanID);
                            s.setAttribute("name", "authorizeFormSubmmit"+PlanID);
                            s.setAttribute("class", "d-none");

                        // Append the form elements to the form
                        dynamicForm.appendChild(s);
                        
                        // Append the form to the HTML
                        jQuery("#dynamicForm"+PlanID).html(dynamicForm);

                        jQuery('#msgBlock'+PlanID).addClass('alert-warning');
                        jQuery('#alertTitle'+PlanID).html('Warning!');
                        jQuery('#msgContent'+PlanID).html(response.msg);
                        jQuery('#msgBlock'+PlanID).addClass('show')
                        jQuery('#msgContent'+PlanID).append('You will redirect to your bank. Please not close the page');
                        
                        jQuery('#loading'+PlanID).removeClass('show');
                        jQuery('#spinner'+PlanID).removeClass('d-none');

                        /**
                         * Redirect the authorizeFormXXXX to bank for Authorize
                         */
                        dynamicForm.submit();

                    } else {
                        jQuery('#msgBlock'+PlanID).addClass('alert-warning');
                        jQuery('#alertTitle'+PlanID).html('Error!');
                        jQuery('#msgContent'+PlanID).html(response.msg);
                        jQuery('#msgBlock'+PlanID).addClass('show');
                        jQuery('#addSubscriptionButton'+PlanID).show();
                        jQuery('#loading'+PlanID).removeClass('show');
                    }                    
                }
            },
            error: function( error ){
                jQuery('#msgBlock'+PlanID).addClass('alert-warning');
                jQuery('#alertTitle'+PlanID).html('Error!');
                jQuery('#msgContent'+PlanID).html(response.msg);
                jQuery('#msgBlock'+PlanID).addClass('show');
                jQuery('#addSubscriptionButton'+PlanID).show();
                jQuery('#loading'+PlanID).removeClass('show');
            }
        });
    }
}

function VerifyAuthAction(buttonAct, planId) {
    var buttonAct = buttonAct;
    var form = buttonAct.closest("form");
    var formId = form.id;
    var formData =jQuery(form).serializeArray();
    

    data = {
        action : 'doVerifyAuth',
        formData : formData,
    };

    jQuery.ajax({
        url: frontAjax.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function( response ){
            if(response.status) {
                jQuery('#msgBlock-'+formId).addClass('alert-success');
                jQuery('#alertTitle-'+formId).html('Congratulation!');
                jQuery('#msgContent-'+formId).html(response.msg);
                jQuery('#loading-'+formId).removeClass('show');
                jQuery('#msgBlock-'+formId).addClass('show');
                jQuery('#'+planId).addClass('d-none'); // Remove Subscribe button
            } else {
                jQuery('#msgBlock-'+formId).addClass('alert-warning');
                jQuery('#alertTitle-'+formId).html('Warning!');
                jQuery('#msgContent-'+formId).html(response.msg);
                jQuery('#loading-'+formId).removeClass('show');
                jQuery('#msgBlock-'+formId).addClass('show');
            }
        },
        error: function( error ){
            jQuery('#msgBlock-'+formId).addClass('alert-danger');
            jQuery('#alertTitle-'+formId).html('Error!');
            jQuery('#msgContent-'+formId).html(response.msg);
            jQuery('#loading-'+formId).removeClass('show');
            jQuery('#msgBlock-'+formId).addClass('show');
        },
        complete: function (data) {
            // do nothing
        }
    });

}

function frontSubscriptionNextPayment(subscriptionId, palanId, subscriberName) {
    jQuery('#subscriberName').html(subscriberName);

    getNextPaymentData = {
        action : 'getMyNextPayment',
        subscriptionId: subscriptionId,
    }

    jQuery.ajax({
        url: frontAjax.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: getNextPaymentData,
        success: function( response ){
            if(response.status) {
                jQuery("#nextPaymentByFrontLoading").hide();
                if(response.data.isValid) {
                    jQuery("#nextPaymentStatus").html('Active');
                } else {
                    jQuery("#nextPaymentStatus").html('Inactive');
                }
                
                jQuery("#nextPaymentDate").html(response.data.nextPayment);
                
            } else {
                jQuery('#msgBlock').addClass('alert-warning');
                jQuery('#alertTitle').html('Error!');
                jQuery('#msgContent').html(response.msg);
                jQuery('#msgBlock').addClass('show');
            }
        },
        error: function( error ){
            jQuery("#nextPaymentByFrontLoading").hide();
            jQuery('#msgBlock').addClass('alert-warning');
            jQuery('#alertTitle').html('Error!');
            jQuery('#msgContent').html(response.msg);
            jQuery('#msgBlock').addClass('show');
        }
    });

    jQuery('#nextPaymentModal').modal('toggle');
    jQuery('#nextPaymentModal').modal('show');
}



jQuery(document).on("click", ".unsubscriptionMyAccounButton", function () {
    var planTitle = jQuery(this).data('plantitle');
    var userId = jQuery(this).data('userid'); 
    var subscriptionId = jQuery(this).data('subscriptionid');

    jQuery('#PlanTitle').html(planTitle);
    jQuery('#Id').val(userId);
    jQuery('#Subscription_Id').val(subscriptionId);

});


function updateMyAccountDetails() {
    // alert('Need changes - Id need to replaced by name');
    var SubscriptionId = jQuery("#SubscriptionId").val();
    var UserID = jQuery("#username").val();
    var Pass = jQuery("#password").val();
    var Name = jQuery("#firstName").val();
    var LastName = jQuery("#lastName").val();
    var Email = jQuery("#email").val();
    var Address  = jQuery("#address").val();
    var City = jQuery("#state").val();
    var Tel = jQuery("#tel").val();
   
    data = {
        action : 'updateSubscriberAccountDetails',
        SubscriptionId : SubscriptionId,
        UserID : UserID,
        Pass : Pass,
        Name : Name,
        LastName : LastName,
        Email : Email,
        Address : Address,
        City : City,
        Tel : Tel,
    };

    jQuery.ajax({
        url: frontAjax.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function( response ){
            if(response.status) {
                jQuery('#myAccountForm').hide();
                jQuery('#msgBlock').addClass('alert-success');
                jQuery('#alertTitle').html('Congratulation!');
                jQuery('#msgContent').html(response.msg);
                jQuery('#msgBlock').addClass('show');
                jQuery('#loading').removeClass('show');
            } else {
                jQuery('#msgBlock').addClass('alert-warning');
                jQuery('#alertTitle').html('Error!');
                jQuery('#msgContent').html(response.msg);
                jQuery('#myAccountGoToHome').hide();
                jQuery('#msgBlock').addClass('show');
                jQuery('#addSubscriptionButton').show();
                jQuery('#loading').removeClass('show');
            }
        },
        error: function( error ){
            jQuery('#msgBlock').addClass('alert-warning');
            jQuery('#alertTitle').html('Error!');
            jQuery('#msgContent').html(response.msg);
            jQuery('#myAccountGoToHome').hide();
            jQuery('#msgBlock').addClass('show');
            jQuery('#addSubscriptionButton').show();
            jQuery('#loading').removeClass('show');
        }
    });
}

/* luhn_checksum
 * Implement the Luhn algorithm to calculate the Luhn check digit.
 * Return the check digit.
 */
function luhn_checksum(code) {
    var len = code.length
    var parity = len % 2
    var sum = 0
    for (var i = len-1; i >= 0; i--) {
        var d = parseInt(code.charAt(i))
        if (i % 2 == parity) { d *= 2 }
        if (d > 9) { d -= 9 }
        sum += d
    }
    return sum % 10
}

/* luhn_validate
 * Return true if specified code (with check digit) is valid.
 */
function luhn_validate(fullcode) {
    return luhn_checksum(fullcode) == 0
}