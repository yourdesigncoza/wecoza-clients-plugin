/**
 * Clients Table JavaScript
 * Handles sorting, search, and table interactions for the clients display
 */
(function($) {
    'use strict';

    // Initialize on document ready
    $(document).ready(function() {
        initClientTable();
    });

    function initClientTable() {
        const $table = $('#clients-table');
        const $searchForm = $('#clients-search-form');
        
        if (!$table.length) return;

        // Initialize sortable columns
        initSortableColumns($table);
        
        // Initialize search form enhancements
        initSearchForm($searchForm);
        
        // Initialize row hover effects
        initRowInteractions($table);
    }

    function initSortableColumns($table) {
        $table.find('th[data-sortable="true"]').each(function() {
            const $th = $(this);
            const $indicator = $th.find('.sort-indicator');
            
            // Add cursor pointer and click handler
            $th.css('cursor', 'pointer').on('click', function() {
                handleSort($th, $indicator);
            });
        });
    }

    function handleSort($th, $indicator) {
        const sortKey = $th.data('sort-key');
        const sortType = $th.data('sort-type');
        const currentUrl = new URL(window.location);
        const currentSort = currentUrl.searchParams.get('order_by') || 'client_name';
        const currentDir = currentUrl.searchParams.get('order_dir') || 'asc';
        
        // Determine new sort direction
        let newDir = 'asc';
        if (currentSort === sortKey && currentDir === 'asc') {
            newDir = 'desc';
        }
        
        // Update URL
        currentUrl.searchParams.set('order_by', sortKey);
        currentUrl.searchParams.set('order_dir', newDir);
        
        // Navigate to new URL
        window.location.href = currentUrl.toString();
    }

    function initSearchForm($searchForm) {
        if (!$searchForm.length) return;
        
        const $searchInput = $searchForm.find('#client_search');
        
        // Auto-submit on Enter with debouncing
        let searchTimeout;
        $searchInput.on('keyup', function(e) {
            if (e.which === 13) { // Enter key
                e.preventDefault();
                $searchForm.submit();
            } else if (e.which !== 13) {
                // Clear any existing timeout
                clearTimeout(searchTimeout);
                
                // Set new timeout for auto-search (optional)
                searchTimeout = setTimeout(function() {
                    if ($searchInput.val().length >= 3 || $searchInput.val().length === 0) {
                        // Auto-search could be implemented here if desired
                        // $searchForm.submit();
                    }
                }, 500);
            }
        });
        
        // Clear search on escape key
        $searchInput.on('keydown', function(e) {
            if (e.which === 27) { // Escape key
                $searchInput.val('');
                $searchForm.submit();
            }
        });
    }

    function initRowInteractions($table) {
        // Add hover effects to rows
        $table.find('tbody tr').hover(
            function() {
                $(this).addClass('table-active');
            },
            function() {
                $(this).removeClass('table-active');
            }
        );
        
        // Add click to select functionality (optional)
        $table.find('tbody tr').on('click', function(e) {
            // Don't select row if clicking on links, buttons, or dropdown toggles
            if ($(e.target).closest('a, button, .dropdown').length) {
                return;
            }
            
            // Toggle selection
            $(this).toggleClass('table-selected');
        });
    }

    // Global functions for table actions
    window.refreshClients = function() {
        window.location.reload();
    };

    window.exportClients = function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = ajaxurl; // WordPress AJAX URL
        
        const nonce = document.createElement('input');
        nonce.type = 'hidden';
        nonce.name = 'nonce';
        nonce.value = wecozaClients.nonce;
        
        const action = document.createElement('input');
        action.type = 'hidden';
        action.name = 'action';
        action.value = 'export_clients';
        
        form.appendChild(nonce);
        form.appendChild(action);
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    };

    window.deleteClient = function(clientId, clientName) {
        if (confirm('Are you sure you want to delete "' + clientName + '"? This action cannot be undone.')) {
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'delete_client',
                    client_id: clientId,
                    nonce: wecozaClients.nonce
                },
                beforeSend: function() {
                    // Show loading state
                    $(`tr[data-client-id="${clientId}"]`).addClass('opacity-50');
                },
                success: function(response) {
                    if (response.success) {
                        // Animate row removal
                        const $row = $(`tr[data-client-id="${clientId}"]`);
                        $row.fadeOut(400, function() {
                            $(this).remove();
                            
                            // Check if table is empty and reload if needed
                            if ($('#clients-table tbody tr').length === 0) {
                                window.location.reload();
                            }
                        });
                    } else {
                        alert('Error: ' + (response.data.message || 'Failed to delete client'));
                        // Restore row opacity
                        $(`tr[data-client-id="${clientId}"]`).removeClass('opacity-50');
                    }
                },
                error: function() {
                    alert('An error occurred while deleting the client.');
                    // Restore row opacity
                    $(`tr[data-client-id="${clientId}"]`).removeClass('opacity-50');
                }
            });
        }
    };

})(jQuery);
