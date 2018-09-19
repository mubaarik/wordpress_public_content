/*exported region_config_option, CheckAllRegions, awpcp_localize_region_toggle_visibility*/

/* Code formerly inline in awpcp_region_control_manage_regions function */

/**
 * Used in Manage Regions section.
 */
function region_config_option(idElement){
    var tot_tab = 5,
        tab = document.getElementById('tab'+idElement),
        region_manage_option = document.getElementById('regionopt');

    for ( var i = 1; i <= tot_tab; i = i + 1 ) {
        if (i === idElement) {
            /*set class for active tab */
            tab.className = 'selected';

            if (idElement==='') {
                /*hide all but the form the default form where there is no idElement value (in this case the look up region form )*/
                document.getElementById('lookupregion').style.display='block';
                document.getElementById('addregion').style.display='none';
                document.getElementById('disableregion').style.display='none';
                document.getElementById('enableregion').style.display='none';
                document.getElementById('localizemodule').style.display='none';
            }

            if ( idElement === 1 ) {
                /*hide all but the form that is associated with idElement 1 (in this case the look up region form )*/
                document.getElementById('lookupregion').style.display='block';
                document.getElementById('addregion').style.display='none';
                document.getElementById('disableregion').style.display='none';
                document.getElementById('enableregion').style.display='none';
                document.getElementById('localizemodule').style.display='none';
            }

            if ( idElement === 2 ) {
                /*hide all but the form that is associated with idElement 2 (in this case the add new region(s) form )*/
                document.getElementById('lookupregion').style.display='none';
                document.getElementById('addregion').style.display='block';
                document.getElementById('disableregion').style.display='none';
                document.getElementById('enableregion').style.display='none';
                document.getElementById('localizemodule').style.display='none';
            }

            if ( idElement === 3 ) {
                /*hide all but the form that is associated with idElement 3 (in this case the disable regions form )*/
                document.getElementById('lookupregion').style.display='none';
                document.getElementById('addregion').style.display='none';
                document.getElementById('disableregion').style.display='block';
                document.getElementById('enableregion').style.display='none';
                document.getElementById('localizemodule').style.display='none';
            }

            if ( idElement === 4 ) {
                /*hide all but the form that is associated with idElement 4 (in this case the Enable Regions form )*/
                document.getElementById('lookupregion').style.display='none';
                document.getElementById('addregion').style.display='none';
                document.getElementById('disableregion').style.display='none';
                document.getElementById('enableregion').style.display='block';
                document.getElementById('localizemodule').style.display='none';
            }

            if ( idElement === 5 ) {
                /*hide all but the form that is associated with idElement 5 (in this case the Localize module form )*/
                document.getElementById('lookupregion').style.display='none';
                document.getElementById('addregion').style.display='none';
                document.getElementById('disableregion').style.display='none';
                document.getElementById('enableregion').style.display='none';
                document.getElementById('localizemodule').style.display='block';
            }

            /*set value for the hidden input element */
            region_manage_option.value = idElement;
        } else {
            /*unset class for non active tabs */
            document.getElementById('tab'+i).className = '';
        }
    }
}

/**
 * Used in Manage Regions section.
 */
function CheckAllRegions(field) {
    for ( var i = 0; i < field.elements.length; i = i + 1 ) {
        field.elements[i].checked = !field.elements[i].checked;
    }
}

/**
 * Used in Manage Regions section.
 */
function awpcp_localize_region_toggle_visibility(id) {
    var e = document.getElementById(id);
    if (e.style.display === 'block') {
        e.style.display = 'none';
    } else  {
        e.style.display = 'block';
    }
}
