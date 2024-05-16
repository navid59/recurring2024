<div id="unsubscribeModal" class="modal fade" data-backdrop-limit="1" tabindex="-1" aria-labelledby="recurringModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
              <h2 class="modal-title" id="unsubscribeModalLabel">
                <?php echo __('Subscription management','ntpRp');?>
                <div id="unsubscriptionByAdminLoading" class="spinner-grow" style="color: darkgreen" role="status" aria-hidden="true"></div>
              </h2>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div>
                <?php
                echo __('The ', 'ntpRp');
                ?>
                <strong>
                    <span id="unSubscriberName"></span>
                </strong>
                <?php
                echo __(' from ', 'ntpRp');
                ?>
                <strong>
                    <span id="unSubscriberPlanTitle"></span>
                </strong>
              </div>        
              <div id="subscriberDetails">
                <p>
                  <?php echo __('The current status is ', 'ntpRp'); ?> <strong><span id="userCurrentStatus"> - </span></strong>
                  <?php echo __('and payment scheduled for date', 'ntpRp'); ?> <strong><span id="userPaymentDate"> - </span><strong>
                </p>
              </div>
              <div id="textContinueUnsubscribe" class="alert-light">
                <h6><?php echo __('To unsubscribe click on unsubscribe button.','ntpRp').__('Otherwise close the window','ntpRp');?></h6>
              </div>
              <div id="textAlreadyUnsubscribed" class="alert-light">
                <h6><?php echo __('The user is already unscubscribed.','ntpRp')?></h6>
              </div>
              <div id="textAlreadySuspended" class="alert-light">
                <h6><?php echo __('The user subscription is suspended.To resubscription click on resubscribe button','ntpRp')?></h6>
              </div>
              <div class="modal-footer">
                  <div id="unsubscriptionByAdminActionLoading" class="spinner-border text-success" role="status">
                    <span class="sr-only">Loading...</span>
                  </div>
                  <button id="unsubscriptionByAdminButton" class="btn btn-secondary" type="submit" >Unsubscribe</button>
                  <button id="resubscriptionByAdminButton" class="btn btn-secondary" type="submit" >Resubscribe</button>
                  <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
              </div>
              <div class="alert alert-dismissible fade" id="unsubscribeAdminMsgBlock" role="alert">
                  <strong id="unsubscribeAdminAlertTitle">!</strong> <span id="unsubscribeAdminMsgContent"></span>.
              </div>
            </div>
        </div>
    </div>
</div>