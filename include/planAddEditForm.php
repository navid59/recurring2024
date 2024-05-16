<div class="jumbotron">
    <form id="recurring-plan-form" method="post" class="">
        <div class="form-group">
            <label for="planTitile">Plan Title</label>
            <input type="text" class="form-control" id="planTitile" pattern=".{3,}" title="3 characters minimum" placeholder="<?php echo __('Your plan title', 'ntpRp');?>" required>
            <div class="valid-feedback"><?php echo __('Valid plan title.','ntpRp'); ?></div>
            <div class="invalid-feedback"><?php echo __('Please fill out plan title.','ntpRp'); ?></div>
        </div>
        <div class="form-group">
            <label for="planDescription">Plan Description</label>
            <textarea class="form-control" id="planDescription" rows="3" minlength="10" title="10 characters minimum" placeholder="<?php echo __('The short description of Plan', 'ntpRp');?>" required></textarea>
            <div class="valid-feedback"><?php echo __('Valid plan description.','ntpRp'); ?></div>
            <div class="invalid-feedback"><?php echo __('Please fill out plan description.','ntpRp'); ?></div>
        </div>
        <div class="form-row">
            <input type="hidden" id="RecurrenceType" name="RecurrenceType" value="Dynamic" readonly>
            <div class="form-group col-md-2">
                <label for="FrequencyType"><?php echo __('Frequency Type','ntpRp'); ?></label>
                <select id="FrequencyType" class="form-control" required>
                    <option value=""><?php echo __('Choose...','ntpRp'); ?></option>
                    <option value="Day"><?php echo __('Daily','ntpRp'); ?></option>
                    <option value="Month"><?php echo __('Monthly','ntpRp'); ?></option>
                    <option value="Year"><?php echo __('Yearly','ntpRp'); ?></option>
                </select>
                <div class="valid-feedback"><?php echo __('Valid frequency type.','ntpRp'); ?></div>
                <div class="invalid-feedback"><?php echo __('Please choose frequency type.','ntpRp'); ?></div>
            </div>
            <div class="form-group col-md-2">
                <label for="FrequencyValue"><?php echo __('Frequency Value','ntpRp'); ?></label>
                <input type="text" class="form-control" id="FrequencyValue" value="1" pattern="[0-9]{1,4}" title="Only number greater than zero" placeholder="<?php echo __('Your plan Frequency Value', 'ntpRp');?>" required>
                <div class="valid-feedback"><?php echo __('Valid plan frequency value.','ntpRp'); ?></div>
                <div class="invalid-feedback"><?php echo __('Please fill out plan frequency value.','ntpRp'); ?></div>
            </div>
        <!-- </div>
        <div class="form-row"> -->
            <div class="form-group col-md-2">
                <label for="Amount"><?php echo __('Amount (RON)','ntpRp'); ?></label>
                <input type="text" class="form-control" id="Amount" pattern="[0-9]+(\.[0-9]{1,2})?%?" title="This must be a number with up to 2 decimal places and/or" placeholder="<?php echo __('The plan value', 'ntpRp');?>" required>
                <div class="valid-feedback"><?php echo __('Valid plan amount.','ntpRp'); ?></div>
                <div class="invalid-feedback"><?php echo __('Please fill out plan amount.','ntpRp'); ?></div>
            </div>
            <input type="hidden" id="Currency" name="Currency" value="RON" readonly>
            <div class="form-group col-md-2">
                <label for="GracePeriod"><?php echo __('Grace Period','ntpRp'); ?></label>
                <input type="text" class="form-control" id="GracePeriod" value="0"  pattern="[0-9]{1,}" title="Only number" placeholder="<?php echo __('The grace period', 'ntpRp');?>" required>
                <div class="valid-feedback"><?php echo __('Valid plan grace period.','ntpRp'); ?></div>
                <div class="invalid-feedback"><?php echo __('Please fill out plan grace period.','ntpRp'); ?></div>
            </div>
            <div class="form-group col-md-4">
                <label for="InitialPayment"><?php echo __('Initial Payment','ntpRp'); ?></label><br>
                <p>
                    <input type="checkbox" class="form-control" id="InitialPayment" value="true">
                    <?php echo __('check this box if payment is mandatory at subscription time','ntpRp');?>
                </p>
            </div>
        </div>
        <hr>
        <div class="form-row">
            <div class="form-group col-md-4">
                <button type="submit" id="addPlan" class="btn btn-primary"><?php echo __('Submmit', 'ntpRp');?></button>
                <div id="addPlanLoading" class="spinner-border" role="status" style="display: none;">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>
    </form>

    <div id="msgBlock" class="alert alert-success fade" role="alert">
        <h4 class="alert-heading"><strong id="alertTitle">!</strong></h4>
        <p><span id="msgContent"></span></p>
        <a href="admin.php?page=recurring_plan&tab=add_plan"  id="addNewPlan" class="btn btn-primary fade" role="button"><?php echo __('Add new plan', 'ntpRp');?></a>
    </div>
</div>