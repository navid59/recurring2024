<div id="deletePlanModal" class="modal fade" id="recurringModal" tabindex="-1" aria-labelledby="recurringModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
              <h2 class="modal-title" id="recurringModalLabel"><?php echo __('Delete plan','ntpRp');?></h2>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                <span id="msgDelete"></span>
                <form id="recurring-delete-plan-form" method="post" class="was-validated">
                    
                    <div class="form-group">
                        <input type="hidden" class="form-control" id="planId">
                    </div>
                    <div class="form-group">
                        <label for="unsubscribe"><?php echo __('','ntpRp'); ?></label><br>
                        <p>
                            <input type="checkbox" class="form-control" id="unsubscribe" value="true">
                            <?php echo __('check if subscriptions of this plan must be unsubscribe by deleting this plan.','ntpRp');?>
                        </p>
                    </div>
                    <div class="form-group">
                        <label for="conditions"><?php echo __('Term & Conditions','ntpRp'); ?></label><br>
                        <p>
                            <input type="checkbox" class="form-control" id="conditions" value="true" required>
                            <?php echo __('Accept to inform subscribers about deleting this plan.','ntpRp');?>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                        <button type="submit" id="deletePlan" class="btn btn-danger"><?php echo __('Delete', 'ntpRp')?></button>
                        <div id="delPlanLoading" class="spinner-border" role="status" style="display: none;">
                            <span class="sr-only">Deleting...</span>
                        </div>
                    </div>
                    <div class="alert alert-dismissible fade" id="msgBlock" role="alert">
                        <strong id="alertTitle">!</strong> <span id="msgContent"></span>.
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>