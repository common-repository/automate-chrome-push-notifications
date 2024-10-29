jQuery(document).ready(function () {

    jQuery('#aklamator_chrome_push_save').on('click', function (event) {
        var aklapraplicationID = jQuery('#aklamatorChromeApplicationID');
        if (aklapraplicationID.val() == "")
        {
            alert("Paste your Aklamator Application ID");
            aklapraplicationID.focus();
            event.preventDefault();
        }
    });




    jQuery('#aklamatorApplicationID').on('input', function ()
    {
        jQuery('#aklamator_error').css('display', 'none');
    });

    jQuery('#aklamator_login_button').click(function () {
        var akla_login_window = window.open(signup_url,'_blank');
        var aklamator_interval = setInterval(function() {
            var aklamator_hash = akla_login_window.location.hash;
            var aklamator_api_id = "";
            if (akla_login_window.location.href.indexOf('aklamator_wordpress_api_id') !== -1) {

                aklamator_api_id = aklamator_hash.substring(28);
                jQuery("#aklamatorChromeApplicationID").val(aklamator_api_id);
                akla_login_window.close();
                clearInterval(aklamator_interval);
                jQuery('#aklamator_error').css('display', 'none');
            }
        }, 1000);

    });
    
    if (jQuery('table').hasClass('dynamicTable')) {
        jQuery('.dynamicTable').dataTable({

            "bFilter": false,
            "bPaginate": false,
            "bJQueryUI": false,
            "bAutoWidth": false,

            "aaSorting": [[0, "desc"]],

            "aoColumnDefs": [

                {"sWidth": "30px", "aTargets": [0]}

            ]

        });
    }
});