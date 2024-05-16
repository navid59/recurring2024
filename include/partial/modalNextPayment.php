<div id="nextPaymentModal" class="modal fade" data-backdrop-limit="1" tabindex="-1" aria-labelledby="recurringModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
              <h2 class="modal-title" id="nextPaymentModalLabel"><?php echo __('Payment Schedule','ntpRp');?></h2>
              <div id="nextPaymentByAdminLoading" class="spinner-grow" style="color: darkgreen" role="status" aria-hidden="true"></div>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div id="">
                <?php
                echo __('Next payment schedule of ', 'ntpRp');
                ?>
                <strong>
                    <span id="subscriberName"></span>
                </strong>
                <?php
                echo __(' for ', 'ntpRp');
                ?>
                <strong>
                    <span id="thePlanTitle"></span>
                </strong>
              </div>        
              <div>
                <h5><?php echo __('Date', 'ntpRp'); ?> : <span id="nextPaymentDate"> - </span></h5>
                <h5><?php echo __('Status', 'ntpRp'); ?> : <span id="nextPaymentStatus"> - </span></h5>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
              </div>
              <div class="alert alert-dismissible fade" id="msgBlock" role="alert">
                  <strong id="alertTitle">!</strong> <span id="msgContent"></span>.
              </div>
            </div>
        </div>
    </div>
</div>