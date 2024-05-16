<div id="subscriberHistorytModal" class="modal fade" tabindex="-1" aria-labelledby="recurringModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
              <h2 class="modal-title" id="nextPaymentModalLabel"><?php echo __('History','ntpRp');?></h2>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">            
              <div class="card col" style="">
                    <div class="card-body">
                        <h3 class="card-title"><?php echo __('User name : ','ntpRp');?><span id="who"></span></h3>
                        <table id="" class="table" width="100%">
                            <thead>
                            <tr>
                                <th><?php echo __('Date','ntpRp');?></th>
                                <th><?php echo __('Title, Amount & Transaction ID','ntpRp');?></th>
                                <!-- <th><?php echo __('Transaction ID','ntpRp');?></th> -->
                                <th><?php echo __('Comment','ntpRp');?></th>
                                <th><?php echo __('Status','ntpRp');?></th>
                            </tr>
                            </thead>
                            <tbody id="subscriberPaymentHistoryList">
                                <!-- History List -->
                            </tbody>
                            <tfoot>
                            <tr>
                                <th><?php echo __('Date','ntpRp');?></th>
                                <th><?php echo __('Title, Amount & Transaction ID','ntpRp');?></th>
                                <!-- <th><?php echo __('Transaction ID','ntpRp');?></th> -->
                                <th><?php echo __('Comment','ntpRp');?></th>
                                <th><?php echo __('Status','ntpRp');?></th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
              </div>
            </div>
        </div>
    </div>
</div>