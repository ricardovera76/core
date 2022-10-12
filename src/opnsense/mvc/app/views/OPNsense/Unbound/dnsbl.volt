{#
 # Copyright (c) 2019 Deciso B.V.
 # Copyright (c) 2019 Michael Muenz <m.muenz@gmail.com>
 # All rights reserved.
 #
 # Redistribution and use in source and binary forms, with or without modification,
 # are permitted provided that the following conditions are met:
 #
 # 1. Redistributions of source code must retain the above copyright notice,
 #    this list of conditions and the following disclaimer.
 #
 # 2. Redistributions in binary form must reproduce the above copyright notice,
 #    this list of conditions and the following disclaimer in the documentation
 #    and/or other materials provided with the distribution.
 #
 # THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 # INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 # AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 # AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 # OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 # SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 # INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 # CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 # ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 # POSSIBILITY OF SUCH DAMAGE.
 #}

<script>
   $(document).ready(function() {
       function nxdomain_toggle() {
           $('input[id="unbound.dnsbl.nxdomain"]').is(':checked') ?
               $('input[id="unbound.dnsbl.address"]').attr('disabled', 'disabled') :
               $('input[id="unbound.dnsbl.address"]').removeAttr('disabled');
       }

       var data_get_map = {'frm_dnsbl_settings':"/api/unbound/settings/get"};
       mapDataToFormUI(data_get_map).done(function(data){
           formatTokenizersUI();
           $('.selectpicker').selectpicker('refresh');

           $('input[id="unbound.dnsbl.nxdomain"]').click(function() {
               nxdomain_toggle();
           });
           nxdomain_toggle();
       });

       $("#saveAct").SimpleActionButton({
          onPreAction: function() {
              const dfObj = new $.Deferred();
              saveFormToEndpoint("/api/unbound/settings/set", 'frm_dnsbl_settings', function(){
                  dfObj.resolve();
              });
              return dfObj;
          }
      });

      updateServiceControlUI('unbound');
   });
</script>

<div class="content-box" style="padding-bottom: 1.5em;">
    {{ partial("layout_partials/base_form",['fields':dnsblForm,'id':'frm_dnsbl_settings'])}}
    <div class="col-md-12">
        <hr />
        <button class="btn btn-primary" id="saveAct"
                data-endpoint='/api/unbound/service/dnsbl'
                data-label="{{ lang._('Apply') }}"
                data-error-title="{{ lang._('Error updating blocklists') }}"
                type="button">
        </button>
    </div>
</div>
