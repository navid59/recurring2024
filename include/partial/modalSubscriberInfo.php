<div id="subscriberInfotModal" class="modal fade" tabindex="-1" aria-labelledby="recurringModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
              <h2 class="modal-title" id="nextPaymentModalLabel"><?php echo __('Subscriber Details','ntpRp');?></h2>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                <div class="card col" style="">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo __('Personal information', 'ntpRp');?></h3>
                        <h6><span class="font-weight-bold"><?php echo __('First Name', 'ntpRp');?> :</span><span id="SubscriberInfo_FirstaName"> - </span></h6>
                        <h6><span class="font-weight-bold"><?php echo __('Last Name', 'ntpRp');?> :</span><span id="SubscriberInfo_LastName"> - </span></h6>
                        <h6><span class="font-weight-bold"><?php echo __('User ID', 'ntpRp');?> :</span><span id="SubscriberInfo_UserId"> - </span></h6>
                        <h6><span class="font-weight-bold"><?php echo __('Tel', 'ntpRp');?> :</span><span id="SubscriberInfo_Tel"> - </span></h6>
                        <h6><span class="font-weight-bold"><?php echo __('Email', 'ntpRp');?> :</span><span id="SubscriberInfo_Email"> - </span></h6>
                        <h6><span class="font-weight-bold"><?php echo __('Address', 'ntpRp');?> :</span><span id="SubscriberInfo_Address"> - </span></h6>
                        <h6><span class="font-weight-bold"><?php echo __('City', 'ntpRp');?> :</span><span id="SubscriberInfo_City"> - </span></h6>
                    </div>
                </div>
                <div class="card col" style="">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo __('Subscription List','ntpRp');?></h3>
                        <table id="subscriberInfoDtBasic" class="table" width="100%">
                            <thead>
                            <tr>
                                <th><?php echo __('Title','ntpRp');?></th>
                                <th><?php echo __('Amount','ntpRp');?></th>
                                <th><?php echo __('Date','ntpRp');?></th>
                                <th><?php echo __('Status','ntpRp');?></th>
                                <th><?php echo __('Action','ntpRp');?></th>
                                <th><?php echo __('Next payment','ntpRp');?></th>
                            </tr>
                            </thead>
                            <tbody id="subscriberPlanList">
                                <!-- Plan List -->
                            </tbody>
                            <tfoot>
                            <tr>
                                <th><?php echo __('Title','ntpRp');?></th>
                                <th><?php echo __('Amount','ntpRp');?></th>
                                <th><?php echo __('Date','ntpRp');?></th>
                                <th><?php echo __('Status','ntpRp');?></th>
                                <th><?php echo __('Action','ntpRp');?></th>
                                <th><?php echo __('Next payment','ntpRp');?></th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
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