jQuery(document).ready(function($) {
    const {ajaxurl, series_id} = ajax_credits_params;

    // Department to Jobs mapping
    const departmentJobs = {
        'Costume & Make-Up': [
            'Costume Designer',
            'Makeup Artist',
            'Hair Stylist',
            'Costume Supervisor',
            'Key Makeup Artist',
            'Key Hair Stylist'
        ],
        'Writing': [
            'Writer',
            'Co-Writer',
            'Short Story',
            'Script Consultant',
            'Writers\' Assistant',
            'Story',
            'Story Editor',
            'Original Series Creator',
            'Junior Story Editor',
            'Senior Story Editor',
            'Story Consultant',
            'Head of Story',
            'Original Concept',
            'Graphic Novel',
            'Story Coordinator',
            'Story Developer'
        ],
        'Crew': [
            'Special Effects',
            'Production Artist',
            'Sequence Supervisor',
            'Photoscience Manager',
            'Post-Production Manager',
            'Video Assist Operator',
            // ... add all crew jobs from your list
            'Graphic Novel Illustrator'
        ],
        'Directing': [
            'Additional Third Assistant Director',
            'Insert Unit Director',
            'Series Director',
            'Insert Unit First Assistant Director',
            'Script Coordinator',
            'Co-Director',
            'Director',
            'Second Unit Director',
            'Assistant Director Trainee',
            'Second Unit First Assistant Director',
            'First Assistant Director (Prep)',
            'Second Assistant Director Trainee',
            'First Assistant Director',
            'Special Guest Director',
            'Second Assistant Director',
            'Field Director',
            'Third Assistant Director',
            'Layout',
            'Stage Director',
            'Script Supervisor',
            'Other',
            'Assistant Director',
            'Continuity',
            'Action Director',
            'Crowd Assistant Director',
            'First Assistant Director Trainee',
            'Second Second Assistant Director',
            'Additional Second Assistant Director'
        ],
        'Lighting': [
            'Rigging Gaffer',
            'Gaffer',
            'Chief Lighting Technician',
            'Rigging Supervisor',
            'Key Rigging Grip',
            'Daily Electrics',
            'Best Boy Electrician',
            'Generator Operator',
            'Lighting Programmer',
            'Directing Lighting Artist',
            'Other',
            'Best Boy Electric',
            'Lighting Artist',
            'O.B. Lighting',
            'Additional Lighting Technician',
            'Standby Rigger',
            'Assistant Electrician'
        ],
        'Production': [
            'Producer',
            'Executive Producer',
            'Line Producer',
            'Production Manager',
            'Unit Production Manager',
            'Production Coordinator'
        ],
        'Actors': [
            'Actor',
            'Actress',
            'Voice Actor',
            'Stunt Double',
            'Body Double',
            'Stand In'
        ],
        'Camera': [
            'Director of Photography',
            'Camera Operator',
            'First Assistant Camera',
            'Second Assistant Camera',
            'Steadicam Operator',
            'Camera Technician'
        ],
        'Art': [
            'Production Designer',
            'Art Director',
            'Set Designer',
            'Set Decorator',
            'Props Master',
            'Scenic Artist'
        ],
        'Sound': [
            'Sound Designer',
            'Sound Mixer',
            'Boom Operator',
            'Sound Editor',
            'Foley Artist',
            'ADR Mixer'
        ],
        'Visual Effects': [
            // ... add all VFX jobs from your list
            'Head of Animation'
        ],
        'Editing': [
            'Editor',
            'Additional Editorial Assistant',
            'First Assistant Picture Editor',
            'Senior Colorist',
            'Editorial Production Assistant',
            'Archival Footage Coordinator',
            'Digital Intermediate Assistant',
            'EPK Editor',
            'Editorial Coordinator',
            'Assistant Editor',
            'Co-Editor',
            'Additional Colorist',
            'Digital Intermediate Colorist',
            'Color Assistant',
            'Dailies Technician',
            'Supervising Film Editor',
            'Digital Colorist',
            'Digital Intermediate',
            'Editorial Consultant',
            'Project Manager',
            '3D Digital Colorist',
            'Digital Intermediate Producer',
            'First Assistant Editor',
            'Color Timer',
            'Negative Cutter',
            'Additional Editor',
            'Assistant Picture Editor',
            'Supervising Editor',
            'Editorial Manager',
            'Atmos Editor',
            'Digital Intermediate Data Wrangler',
            'Other',
            'Associate Editor',
            'Color Grading',
            'Lead Editor',
            'Editorial Services',
            'Additional Editing',
            'Archival Footage Research',
            'Dailies Manager',
            'Dailies Operator',
            'Senior Digital Intermediate Colorist',
            'Digital Color Timer',
            'Digital Intermediate Editor',
            'Digital Conform Editor',
            'Online Editor',
            'Consulting Editor',
            'Stereoscopic Editor'
        ]
    };

    // Update job select options when department changes
    function updateJobOptions(departmentSelect) {
        const department = departmentSelect.val();
        const jobSelect = departmentSelect.closest('.form-row').find('.job-input');

        jobSelect.empty().append($('<option>').val('').text('Select Job'));

        if (department && departmentJobs[department]) {
            departmentJobs[department].sort().forEach(job => {
                jobSelect.append($('<option>').val(job).text(job));
            });
        }
    }

    // Initialize department select
    function initDepartmentSelect(select) {
        select.empty()
            .append($('<option>').val('').text('Select Department'));

        Object.keys(departmentJobs).sort().forEach(dept => {
            select.append($('<option>').val(dept).text(dept));
        });
    }

    // Create credit element function
    function createCreditElement(credit, type) {
        const element = $('<div>').addClass('credit-item').attr('data-id', credit.ID);
        const content = $('<div>').addClass('credit-content');

        // Create edit form
        const editForm = $('<div>').addClass('edit-form').hide();
        const formRow = $('<div>').addClass('form-row');

        const personSearch = $('<input>')
            .addClass('person-search')
            .attr('type', 'text')
            .attr('placeholder', 'Search person...')
            .val(credit.person_name)
            .attr('data-person-id', credit.person);

        formRow.append(personSearch);

        if (type === 'cast') {
            formRow.append(
                $('<input>')
                    .addClass('role-input')
                    .attr('type', 'text')
                    .attr('placeholder', 'Role')
                    .val(credit.job)
            );
        } else {
            const departmentSelect = $('<select>').addClass('department-select');
            const jobSelect = $('<select>').addClass('job-input');

            initDepartmentSelect(departmentSelect);
            departmentSelect.val(credit.department);

            formRow.append(departmentSelect, jobSelect);

            // Initialize job options and set selected value
            setTimeout(() => {
                updateJobOptions(departmentSelect);
                jobSelect.val(credit.job);
            }, 0);

            // Add change event handler for department select
            departmentSelect.on('change', function() {
                updateJobOptions($(this));
            });
        }

        editForm.append(formRow);
        initPersonSearch(personSearch);

        // Create display view
        const displayView = $('<div>').addClass('display-view')
            .append($('<span>').addClass('person-name').text(credit.person_name))
            .append($('<span>').addClass('role').text(type === 'cast' ?
                credit.job : `${credit.department} - ${credit.job}` )
            );

        content.append(displayView).append(editForm);

        const actions = $('<div>').addClass('credit-actions')
            .append($('<button>').addClass('edit-btn').text('Edit'))
            .append($('<button>').addClass('save-btn').text('Save').hide())
            .append($('<button>').addClass('delete-btn').text('Delete'));

        element.append(content).append(actions);

        // Add event listeners
        element.find('.edit-btn').click(function() {
            element.find('.display-view').hide();
            element.find('.edit-form').show();
            $(this).hide();
            element.find('.save-btn').show();
        });

        element.find('.save-btn').click(function() {
            const personId = element.find('.person-search').attr('data-person-id');
            const role = type === 'cast' ?
                element.find('.role-input').val() :
                element.find('.job-input').val();
            const department = type === 'crew' ?
                element.find('.department-select').val() : '';

            saveCredit({
                type,
                person_id: personId,
                role,
                department,
                credit_id: credit.ID,
                series_id: ajax_credits_params.series_id
            }, element);
        });

        element.find('.delete-btn').click(function() {
            deleteCredit(credit.ID, type);
        });

        return element;
    }

    // Initialize forms when modal opens
    $('.edit-credits-btn').click(function() {
        $('#credits-modal').show();
        initAddForm('cast');
        initAddForm('crew');
        loadCredits('cast');
        loadCredits('crew');
    });

    $('.close-modal').click(function() {
        $('#credits-modal').hide();
    });

    // Tab handling
    $('.credits-tabs .tab_item').click(function() {
        $('.credits-tabs .tab_item').removeClass('active');
        $(this).addClass('active');

        const tab = $(this).data('tab');
        $('.tab-content').removeClass('active');
        $(`#${tab}-tab`).addClass('active');
    });

    // Add credit button handling
    $('.add-credit-btn').click(function() {
        const type = $(this).data('type');
        $(`.${type}-form`).toggle();
    });

    // Throttle function
    function throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        }
    }

    // Person search autocomplete with throttling
    function initPersonSearch(input) {
        $(input).autocomplete({
            source: function(request, response) {
                throttle($.ajax({
                    url: ajax_credits_params.ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'search_tv_people',
                        search: request.term
                    },
                    success: function(data) {
                        if (data.success) {
                            response($.map(data.data, function(item) {
                                return {
                                    label: item.title,
                                    value: item.id,
                                    image: item.image
                                };
                            }));
                        }
                    }
                }), 1000);
            },
            minLength: 2,
            select: function(event, ui) {
                $(this).val(ui.item.label);
                $(this).attr('data-person-id', ui.item.value);
                return false;
            }
        }).data("ui-autocomplete")._renderItem = function(ul, item) {
            return $("<li>")
                .append(`<div class="person-result">
                    <div class="person-image">
                        <img src="${item.image}" width="40" height="40" alt="${item.label}">
                    </div>
                    <div class="person-info">
                        <div class="person-name">${item.label}</div>
                    </div>
                </div>`)
                .appendTo(ul);
        };
    }

    $('.person-search').each(function() {
        initPersonSearch(this);
    });

    // Add CSS for person search results
    $('<style>')
        .text(`
            .person-result {
                display: flex;
                align-items: center;
                padding: 5px;
                background: #fff;
                border-bottom: 1px solid #eee;
            }
            .person-result:hover {
                background: #f5f5f5;
            }
            .person-image {
                margin-right: 10px;
            }
            .person-image img {
                border-radius: 50%;
                object-fit: cover;
            }
            .person-info {
                flex: 1;
            }
            .person-name {
                font-weight: 500;
            }
            .ui-autocomplete {
                max-height: 300px;
                overflow-y: auto;
                overflow-x: hidden;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .ui-autocomplete li {
                border-bottom: 1px solid #eee;
            }
            .ui-autocomplete li:last-child {
                border-bottom: none;
            }
        `)
        .appendTo('head');

    // Save credit
    $('.save-credit-btn').click(function() {
        const form = $(this).closest('.add-credit-form');
        const type = form.hasClass('cast-form') ? 'cast' : 'crew';
        const personId = form.find('.person-search').attr('data-person-id');
        const role = type === 'cast' ? form.find('.role-input').val() : form.find('.job-input').val();
        const department = type === 'crew' ? form.find('.department-select').val() : '';
        const creditId = form.attr('data-credit-id');

        if (!personId) {
            alert('Please select a person');
            return;
        }

        if (!role) {
            alert('Please enter a role');
            return;
        }

        if (type === 'crew' && !department) {
            alert('Please select a department');
            return;
        }

        saveCredit({
            type,
            person_id: personId,
            role,
            department,
            credit_id: creditId,
            series_id: ajax_credits_params.series_id
        }, form);
    });

    // Load credits function
    function loadCredits(type) {
        $.ajax({
            url: ajax_credits_params.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'get_tv_credits',
                type: type,
                series_id: ajax_credits_params.series_id
            },
            success: function(response) {
                if (response.success) {
                    const list = $(`.${type}-list`);
                    list.empty();

                    response.data.forEach(credit => {
                        list.append(createCreditElement(credit, type));
                    });
                }
            }
        });
    }

    // Save credit function
    function saveCredit(data, form) {
        $.ajax({
            url: ajax_credits_params.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'save_tv_credit',
                ...data
            },
            success: function(response) {
                if (response.success) {
                    loadCredits(data.type);
                    form.find('input').val('');
                    form.find('select').val('');
                    form.removeAttr('data-credit-id');
                    form.hide();
                }
            }
        });
    }

    // Delete credit function
    function deleteCredit(creditId, type) {
        if (confirm('Are you sure you want to delete this credit?')) {
            $.ajax({
                url: ajax_credits_params.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'delete_tv_credit',
                    credit_id: creditId,
                    type: type
                },
                success: function(response) {
                    if (response.success) {
                        loadCredits(type);
                    }
                }
            });
        }
    }

    // Add this function after the departmentJobs object and before other functions
    function initAddForm(type) {
        const form = $(`.${type}-form`);
        form.find('input').val('');
        form.find('select').val('');
        form.removeAttr('data-credit-id');

        if (type === 'crew') {
            const departmentSelect = form.find('.department-select');
            const jobSelect = form.find('.job-input');

            // Initialize department select if not already initialized
            if (departmentSelect.find('option').length <= 1) {
                initDepartmentSelect(departmentSelect);
            }

            // Add change event handler for department select if not already added
            if (!departmentSelect.data('initialized')) {
                departmentSelect.on('change', function() {
                    updateJobOptions($(this));
                });
                departmentSelect.data('initialized', true);
            }

            // Initialize job select
            jobSelect.empty().append($('<option>').val('').text('Select Job'));
        }

        // Initialize person search if not already initialized
        const personSearch = form.find('.person-search');
        if (!personSearch.data('initialized')) {
            initPersonSearch(personSearch);
            personSearch.data('initialized', true);
        }
    }
});