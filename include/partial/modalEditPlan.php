<div id="editPlanModal" class="modal fade" id="recurringModal" tabindex="-1" aria-labelledby="recurringModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
              <h2 class="modal-title" id="recurringModalLabel"><?php echo __('Edit plan','ntpRp');?></h2>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                <form id="recurring-edit-plan-form" method="post" class="needs-validation" >
                    <div id="editFormAttributes" >
                        <div class="form-group">
                            <input type="hidden" class="form-control" id="editPlanId" value="" readonly="readonly">
                        </div>
                        <div class="form-group">
                            <label for="editPlanTitile">Plan Title</label>
                            <input type="text" class="form-control" id="editPlanTitile" placeholder="<?php echo __('Your plan title', 'ntpRp');?>" required>
                            <div class="valid-feedback"><?php echo __('Valid plan title.','ntpRp'); ?></div>
                            <div class="invalid-feedback"><?php echo __('Please fill out plan title.','ntpRp'); ?></div>
                        </div>
                        <div class="form-group">
                            <label for="editPlanDescription">Plan Description</label>
                            <textarea class="form-control" id="editPlanDescription" rows="3" placeholder="<?php echo __('The short description of Plan', 'ntpRp');?>" required></textarea>
                            <div class="valid-feedback"><?php echo __('Valid plan description.','ntpRp'); ?></div>
                            <div class="invalid-feedback"><?php echo __('Please fill out plan description.','ntpRp'); ?></div>
                        </div>
                        <div class="form-row">
                            <input type="hidden" id="editRecurrenceType" name="editRecurrenceType" class="form-control" value="" readonly>
                            <div class="form-group col-md-2">
                                <label for="editFrequencyType"><?php echo __('Frequency Type','ntpRp'); ?></label>
                                <select id="editFrequencyType" class="form-control" required>
                                    <option value=""><?php echo __('Choose...','ntpRp'); ?></option>
                                    <option value="Day"><?php echo __('Daily','ntpRp'); ?></option>
                                    <option value="Month"><?php echo __('Monthly','ntpRp'); ?></option>
                                    <option value="Year"><?php echo __('Yearly','ntpRp'); ?></option>
                                </select>
                                <div class="valid-feedback"><?php echo __('Valid frequency type.','ntpRp'); ?></div>
                                <div class="invalid-feedback"><?php echo __('Please choose frequency type.','ntpRp'); ?></div>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="editFrequencyValue"><?php echo __('Frequency Value','ntpRp'); ?></label>
                                <input type="text" class="form-control" id="editFrequencyValue" placeholder="<?php echo __('Your plan Frequency Value', 'ntpRp');?>" required>
                                <div class="valid-feedback"><?php echo __('Valid plan frequency value.','ntpRp'); ?></div>
                                <div class="invalid-feedback"><?php echo __('Please fill out plan frequency value.','ntpRp'); ?></div>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="editAmount"><?php echo __('Amount (RON)','ntpRp'); ?></label>
                                <input type="text" class="form-control" id="editAmount" placeholder="<?php echo __('The plan value', 'ntpRp');?>" required>
                                <div class="valid-feedback"><?php echo __('Valid plan amount.','ntpRp'); ?></div>
                                <div class="invalid-feedback"><?php echo __('Please fill out plan amount.','ntpRp'); ?></div>
                            </div>
                            <input type="hidden" id="editCurrency" name="editCurrency" class="form-control" value="" readonly>
                            <div class="form-group col-md-2">
                                <label for="editGracePeriod"><?php echo __('Grace Period','ntpRp'); ?></label>
                                <input type="text" class="form-control" id="editGracePeriod" placeholder="<?php echo __('The grace period', 'ntpRp');?>" required>
                                <div class="valid-feedback"><?php echo __('Valid plan grace period.','ntpRp'); ?></div>
                                <div class="invalid-feedback"><?php echo __('Please fill out plan grace period.','ntpRp'); ?></div>
                            </div>
                            <div class="form-group col-md-3">
                                <label for="editInitialPayment"><?php echo __('Initial Payment','ntpRp'); ?></label><br>
                                <p>
                                    <input type="checkbox" class="form-control" id="editInitialPayment" value="true">
                                    <?php echo __('check this box if payment is mandatory at subscription time','ntpRp');?>
                                </p>
                            </div>
                        </div>
                        <hr>
                        <div class="form-row">
                            <div class="form-group col-md-8">
                                <label for="editConditions"><?php echo __('Term & Conditions','ntpRp'); ?></label><br>
                                <p>
                                    <input type="checkbox" class="form-control" id="editConditions" value="true" required>
                                    <?php echo __('Accept to inform subscribers about modify this plan.','ntpRp');?>
                                </p>
                            </div>    
                            <div class="form-group col-md-4">
                                <button type="submit" id="editPlan" class="btn btn-success"><?php echo __('Submmit', 'ntpRp');?></button>
                                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                                <div id="editPlanLoading" class="spinner-border" role="status" style="display: none;">
                                    <span class="sr-only">Deleting...</span>
                                </div>
                            </div>                        
                        </div>
                    </div>
                    <!-- <div class="form-row col-md-10"> -->
                        <div class="alert alert-dismissible fade " id="editMsgBlock" role="alert">
                            <strong id="editAlertTitle">!</strong> <span id="editMsgContent"></span>.
                        </div>
                    <!-- </div> -->
                </form>
            </div>
        </div>
    </div>
</div>