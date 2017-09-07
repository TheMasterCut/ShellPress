/**
 * Created by jakubkuranda@gmail.com on 2017-07-25.
 */

( function( $ ) {

    $.fn.ShellPressAjaxListTable = function( nonce, ajaxDisplayAction ){

        var ajaxListTable = $( this );

        list = {
            isLocked:           false,
            data:               {
                'nonce':            nonce,
                'action':           ajaxDisplayAction,
                'paged':            '',
                'order':            '',
                'orderBy':          '',
                'search':           '',
                'view':             '',
                'currentActions':   {},
                'selectedItems':    {}
            },
            init:               function(){

                // This will have its utility when dealing with the page number input

                var timer;
                var delay = 500;


                //  ----------------------------------------
                //  CLICK - Pagination links
                //  ----------------------------------------

                ajaxListTable.find( '.tablenav-pages a' ).on( 'click', function(e) {

                    e.preventDefault();

                    if( ! list.isLocked ){

                        list.isLocked = true;   //  Lock callbacks

                        // Simple way: use the URL to extract our needed variables
                        var query = this.search.substring( 1 );

                        //  Writing attributes
                        ajaxListTable.attr( 'data-paged',       list._query( query, 'paged' ) || '1' );

                        list.update();

                    }

                } );

                //  ----------------------------------------
                //  CLICK - Bar actions submit
                //  ----------------------------------------

                ajaxListTable.find( '.bulkactions [type="submit"]' ).on( 'click', function(e) {

                    e.preventDefault();

                    if( ! list.isLocked ) {

                        list.isLocked = true;       //  Lock callbacks

                        list.updateSelectedRows();  //  Get selected rows

                        var actionsGroup = $( this ).closest( '[data-bar-group]' );

                        actionsGroup.find( '[data-action-id]' ).each( function(){

                            var actionSlug;
                            var actionData;
                            var actionOption;

                            if( $( this ).is( 'select' ) ){

                                actionSlug      = $( this ).attr( 'data-action-id' )                             || null;
                                actionData      = $( this ).find( 'option:selected' ).attr( 'data-action-data' )     || null;
                                actionOption    = $( this ).val();

                            } else

                            if( $( this ).is( '[type="submit"]' ) ){

                                actionSlug = $( this ).attr( 'data-action-id' )         || null;
                                actionData = $( this ).attr( 'data-action-data' )           || null;

                            }

                            //  Adding action to request.

                            if( actionSlug && actionOption ){

                                list.data.currentActions[ actionSlug ] = {};
                                list.data.currentActions[ actionSlug ][actionOption] = JSON.parse( actionData );

                            } else

                            if( actionSlug ){

                                list.data.currentActions[ actionSlug ] = JSON.parse( actionData );

                            }

                        } );

                        list.update();

                    }

                } );

                //  ----------------------------------------
                //  CLICK - Sortable link
                //  ----------------------------------------

                ajaxListTable.find( '.manage-column.sortable a, .manage-column.sorted a' ).on( 'click', function(e) {

                    e.preventDefault();

                    if( ! list.isLocked ) {

                        list.isLocked = true;   //  Lock callbacks

                        // Simple way: use the URL to extract our needed variables
                        var query = this.search.substring( 1 );

                        //  Writing attributes
                        list.data.order     = list._query( query, 'order' ) || 'asc';
                        list.data.orderBy   = list._query( query, 'orderby' ) || 'id';

                        list.update();

                    }

                } );

                //  ----------------------------------------
                //  KEYUP - Page number input
                //  ----------------------------------------

                ajaxListTable.find( 'input[name=paged]' ).on( 'keyup', function(e) {

                    if( e.keyCode === 13 ){

                        e.preventDefault();

                        if( ! list.isLocked ) {

                            list.isLocked = true;   //  Lock callbacks

                            //  Wait `delay` before sending request.
                            window.clearTimeout(timer);

                            timer = window.setTimeout(function () {

                                //  Writing attributes
                                list.data.pagesd = parseInt( ajaxListTable.find('input[name="paged"]').val() ) || '1';

                                list.update();

                            }, delay);

                        }

                    }

                } );

                //  ----------------------------------------
                //  KEYUP - Search input
                //  ----------------------------------------

                ajaxListTable.find( 'input[name="search"]' ).on( 'keyup', function(e) {

                    if( e.keyCode === 13 ){

                        e.preventDefault();

                        if( ! list.isLocked ) {

                            list.isLocked = true;   //  Lock callbacks

                            //  Wait `delay` before sending request.
                            window.clearTimeout(timer);

                            timer = window.setTimeout(function () {

                                //  Writing attributes
                                list.data.search    = ajaxListTable.find('input[name="search"]').val() || '';
                                list.data.paged     = 1;   //  Reset pagination

                                list.update();

                            }, delay);

                        }

                    }

                } );

                //  ----------------------------------------
                //  CLICK - Search
                //  ----------------------------------------

                ajaxListTable.find( '.search-box input[type="submit"]' ).on( 'click', function(e) {

                    e.preventDefault();

                    if( ! list.isLocked ) {

                        list.isLocked = true;   //  Lock callbacks

                        list.data.search    = ajaxListTable.find('input[name="search"]').val() || '';
                        list.data.paged     =  1;   //  Reset pagination

                        list.update();
                    }

                } );

                //  ----------------------------------------
                //  CLICK - View
                //  ----------------------------------------

                ajaxListTable.find( '.subsubsub a[data-value]' ).on( 'click', function(e) {

                    e.preventDefault();

                    if( ! list.isLocked ) {

                        list.isLocked = true;   //  Lock callbacks

                        list.data.view      = $( this ).attr( 'data-value' ) || '';
                        list.data.paged     = 1;   //  Reset pagination

                        list.update();
                    }

                } );

                //  ----------------------------------------
                //  CLICK - Row action
                //  ----------------------------------------

                ajaxListTable.find( '.row-actions [data-action-id]' ).on( 'click', function(e) {

                    e.preventDefault();

                    if( ! list.isLocked ) {

                        list.isLocked = true;   //  Lock callbacks

                        var actionSlug = $( this ).attr( 'data-action-id' )        || null;
                        var actionData = $( this ).attr( 'data-action-data' )   || null;

                        if( actionSlug ){

                            list.data.currentActions[ actionSlug ] = JSON.parse( actionData )

                        }

                        list.update();
                    }

                } );

                //  ----------------------------------------
                //  Dismissible notices
                //  ----------------------------------------

                ajaxListTable.find( '.notice.is-dismissible' ).each( function() {

                    var $el = $( this ),
                        $button = $( '<button type="button" class="notice-dismiss"><span class="screen-reader-text"></span></button>' ),
                        btnText = commonL10n.dismiss || '';

                    // Ensure plain text
                    $button.find( '.screen-reader-text' ).text( btnText );
                    $button.on( 'click.wp-dismiss-notice', function( event ) {

                        event.preventDefault();
                        $el.fadeTo( 100, 0, function() {

                            $el.slideUp( 100, function() {

                                $el.remove();

                            });

                        });

                    });

                    $el.append( $button );

                });

                //  ----------------------------------------
                //  Toggle row visibility
                //  ----------------------------------------

                ajaxListTable.find( 'button.toggle-row' ).on( 'click', function( e ){

                    $( this ).closest( 'tr' ).toggleClass( 'is-expanded' );

                } );

            },
            updateSelectedRows: function() {

                list.data.selectedItems = ajaxListTable.find( '.check-column [data-row-checkbox]:checked' ).map( function(){ return JSON.parse( $( this ).attr( 'data-row-checkbox' ) ); } ).get();

            },
            clearTempActions: function() {

                ajaxListTable.find( '[data-action-temp]' ).each( function(){

                    var tempActionId = $( this ).attr( 'data-action-temp' )     || null;

                    if( tempActionId && tempActionId in list.data.currentActions ){

                        delete list.data.currentActions[ tempActionId ];

                    }

                } );

            },
            clearRowSelections: function() {

                list.data.selectedItems = {}

            },
            update:             function() {

                ajaxListTable.find( '.tablenav .clear' ).before( '<div class="spinner is-active"></div>' );

                var request = jQuery.extend( true, {}, list.data );     //  Create deep copy of object

                list.clearTempActions();                                //  Clear temporary data
                list.clearRowSelections();                              //  Clear selections

                $.ajax( {
                    type:   'POST',
                    url:    ajaxurl,
                    data:   request,
                    success: function( response ) {

                        if( parseInt( response ) !== 0 ){

                            response = $.parseJSON( response );

                            ajaxListTable.html( response );

                            list.init();

                        } else {

                            ajaxListTable.html( '<i class="dashicons dashicons-update"></i><i class="dashicons dashicons-lock"></i>' );
                            console.log( "General problem with access to ajax action?" );

                        }

                    },
                    statusCode: {
                        403: function () {

                            ajaxListTable.html( '<i class="dashicons dashicons-update"></i><i class="dashicons dashicons-lock"></i>' );
                            console.log( "You need to refresh your session." );

                        }
                    },
                    fail:   function() {

                        ajaxListTable.html( '<i class="dashicons dashicons-update"></i><i class="dashicons dashicons-welcome-comments"></i>' );
                        console.log( "Got an error while calling ListTable AJAX." );

                    },
                    complete:   function() {

                        list.isLocked = false;          //  Unlock callbacks

                    }
                } );

            },
            _query:         function( query, variable ) {

                var vars = query.split("&");

                for ( var i = 0; i <vars.length; i++ ) {

                    var pair = vars[ i ].split("=");

                    if ( pair[0] === variable ){

                        return pair[1];

                    }

                }

                return false;

            }
        };

        list.update();

    };

}( jQuery ) );