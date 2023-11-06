(function($) {
    $(document).ready(function() {
        // Custom sorting function
        jQuery.extend(jQuery.fn.dataTableExt.oSort, {
          //Status
          "status-pre": function(a) {
            var statusOrder = ['New', 'In Progress', 'Closed'];
            return statusOrder.indexOf($(a).data('status'));
          },
          "status-asc": function(a, b) {
            return a - b;
          },
          "status-desc": function(a, b) {
            return b - a;
          },
          "date-pre": function(a) {
            return parseInt($(a).data('order'));
          },
          "date-asc": function(a, b) {
            return a - b;
          },
          "date-desc": function(a, b) {
            return b - a;
          }
        });

        var user_dash = $('#consult-table').data('user-dash');
        var show_archived = $('#consult-table').data('show-archived');

        user_dash = !!user_dash;
        show_archived = !!show_archived;

        initializeDataTables(user_dash, show_archived);

        $("#consult-table").on("click", ".open-slideover", function() {
          var consultId = $(this).data("consult-id");
          $("#consult-table").trigger("open-slideover", consultId);
        });
    });
    //Message form
    $(document).on('submit', '[id^="consult-message-form"] form', function(event) {
      event.preventDefault(); // prevent the form from submitting
        var consult_id = $('input[name=consult_id]', this).val();

        $('[id^="consult-"][id$="-editor"]').each(function() {
            // get the id of the current element
            var currentId = $(this).attr('id');
            // extract the consult_id and message_id values from the id
            var consult_id = currentId.split('-')[1];
            var message_id = currentId.split('-')[3];

            var editor = tinymce.get('consult-' + consult_id + '-message-' + message_id + '-editor');
            var content = editor.getContent();
    
            
            $(this).html(content); // put original contents back
            editor.remove(); // remove the editor
        });

        // Get the editor instance associated with the specified element ID
        var editor = tinymce.get('message'+consult_id);

        // Get the content of the editor
        var content = editor.getContent();

        

        if (!editor.getContent()) {
            // Prevent the form from submitting
            event.preventDefault();
            
            // Show an error message to the user
            alert('Please fill in the required message field!');
        } else {
            var form_data = {
                'consult_id': $('input[name=consult_id]', this).val(),
                'submitted_by': $('input[name=submitted_by]', this).val(),
                'moderators_assigned': $('input[name=moderators_assigned]', this).val(),
                'message': content,
                'purpose' : 'create_message',
                'action': 'create_consult_message'
            };

            call_ajax(form_data);
        }
    });
    //Note form
    $(document).on('submit', '[id^="consult-note-"] form', function(event) {
      event.preventDefault(); // prevent the form from submitting

        var consult_id = $('input[name=consult_id]', this).val();
        var content = $('textarea[name=note]', this).val();
        var moderator = $('input[name=moderator]', this).val();
        if (!content) {
            // Show an error message to the user
            alert('Please fill in the required message field!');
            return;
        }

        var form_data = {
            'consult_id': consult_id,
            'moderator': moderator,
            'note': content,
            'purpose' : 'create_note',
            'action': 'create_consult_message'
        };

        call_ajax(form_data);

        // reset the form and close the div
        $("[id^='consult-note-'] textarea").val('');
        $("[id^='consult-note-'] form").trigger("reset");
        $("[id^='consult-note-'] div").attr('x-data', '{ open: false }');
    });
    // jQuery
    $(document).on('submit', '[id^="update-mod-note-"] form', function(event) {
        event.preventDefault(); // prevent the form from submitting

        var consult_id = $('input[name=consult_id]', this).val();
        var content = $('textarea[name=note]', this).val();
        var moderator = $('input[name=moderator]', this).val();

        var collaborators = $('input[name="collaborators[]"]:checked', this).map(function() {
            return this.value;
        }).get();

        var form_data = {
            'consult_id': consult_id,
            'moderator': moderator,
            'note': content,
            'collaborators': collaborators,
            'action': 'create_consult_message'
        };
        // Call collaborate_ajax or whatever function
        // Add 'purpose': 'collaborate' to differentiate this call
        form_data.purpose = 'collaborate';
        call_ajax(form_data);
        
        form_data.purpose = 'create_collab_note';
        call_ajax(form_data);

        // reset the form textarea and leave checkboxes as is
        $('[id^="update-mod-note-"] textarea').val('');
    });
    //Message edit form
    $(document).on('submit', 'form[id^="consult-"][id$="-form"]', function(event) {
      event.preventDefault(); // prevent the form from submitting

      // Extract consult_id and message_id from the form id
      var consult_id = parseInt(this.id.split('-')[1]);
      var message_id = parseInt(this.id.split('-')[3]);

      // Get the editor instance associated with the specified element ID
      var editor = tinymce.get('consult-' + consult_id + '-message-' + message_id + '-editor');

        if (!editor) {
          return;
        }

      // Get the content of the editor
      var content = editor.getContent();


      
      
      if (!content) {
        // Show an error message to the user
        alert('Please fill in the required message field!');
        return;
      }
      
      // Get the values of the hidden fields
      var submitted_by = $('input[name="submitted_by"]', this).val();
      var moderators_assigned = $('input[name="moderators_assigned"]', this).val();
      
      // Construct the form data object
      var form_data = {
        'consult_id': consult_id,
        'message_id': message_id,
        'submitted_by': submitted_by,
        'moderators_assigned': moderators_assigned,
        'message': content,
        'purpose': 'edit_message',
        'action': 'create_consult_message'
      };

      // Put the content back into the message div
      var messageDiv = $('#consult-' + consult_id + '-message-' + message_id);
      messageDiv.html(content);
      
      // Remove the editor
      editor.remove();

      // Call the ajax function to submit the form data
      call_ajax(form_data);
    });

    $(document).on('submit', 'form[id^="consult-"][id$="-save"]', function(event) {
      event.preventDefault(); // prevent the form from submitting

      // Extract consult_id and message_id from the form id
      var parts = this.id.split('-');
      var consult_id = parseInt(parts[1]);
      var message_id = parseInt(parts[3]);

      // Get the values of the input fields
      var title = $('input[name="title"]', this).val();
      var consult_id = $('input[name="consult_id"]', this).val();
      var submitted_by = $('input[name="submitted_by"]', this).val();
      var message = $('#' + this.id + ' div.border').html();

      if (!title) {
        // Show an error message to the user
        alert('Please fill in the required title field!');
        return;
      }
      
      // Construct the form data object
      var form_data = {
        'consult_id': consult_id,
        'message_id': message_id,
        'submitted_by': submitted_by,
        'title': title,
        'message': message,
        'purpose': 'save_message',
        'action': 'create_consult_message'
      };

      // Call the ajax function to submit the form data
      call_ajax(form_data);

      // reset the form and close the div
      $('input[name="title"]', this).val('');
    });
    
    $(document).on('submit', '#user-profile-form', function(event) {
      event.preventDefault(); // prevent the form from submitting

      var user_id = $('input[name=user_id]', this).val();
      var first_name = $('input[name=first_name]', this).val();
      var last_name = $('input[name=last_name]', this).val();
      var phone_number = $('input[name=phone_number]', this).val();
      var organization = $('input[name=organization]', this).val();
      var organization_zip_code = $('input[name=organization_zip_code]', this).val();

      if (!first_name || !last_name) {
          // Check required fields and show an error message to the user if necessary
          alert('Please fill in the required fields!');
          return;
      }

      var form_data = {
          'user_id': user_id,
          'first_name': first_name,
          'last_name': last_name,
          'phone_number': phone_number,
          'organization': organization,
          'organization_zip_code': organization_zip_code,
          'action': 'update_user_profile' // replace with your actual action hook
      };

      call_ajax(form_data);
    });

})(jQuery);
function call_ajax(data){
    jQuery.ajax({
        type: 'POST',
        url: ajax_object.ajax_url,
        data: data,
        beforeSend: function() {
            // Show loading spinner
        },
        success: function(response) {

            let responsePHP = JSON.parse(response);

            // Reset form and show success message
            switch(responsePHP.type) {
              case 'create_message':

                call_update_ajax('get_updated_content', data.consult_id, '#consult-replies-', 'get_replies');
              
                // Get the editor instance
                let editor = tinymce.get('message' + data.consult_id);

                // Check if the editor instance exists
                if (editor) {
                  // Set the content of the editor
                  editor.setContent('');
                } else {
                  console.log('Editor instance not found!' + 'message' + data.consult_id);
                }
                break;
              case 'update_status':
                call_update_ajax('get_updated_content', data.consult_id, '#consult-history-', 'get_history');
                break;
              case 'update_moderator':

                break;
              case 'create_note':
                call_update_ajax('get_updated_content', data.consult_id, '#consult-history-', 'get_history');
                break;
              case 'collaborate':
                call_update_ajax('get_updated_content', data.consult_id, '#consult-history-', 'get_history');
                break;
              case 'delete_note':
                call_update_ajax('get_updated_content', data.consult_id, '#consult-history-', 'get_history');
                break;
              case 'update_topic':
                call_update_ajax('get_updated_content', data.consult_id, '#consult-subtopic-', 'update_topic', responsePHP.topic);
                break;
              case 'refreshConsults':
                jQuery('#consult-dock').load(location.href + ' #consult-dock > *', function() {
                  jQuery('#loading-icon').show();
                  tinymce.remove();
                  // Destroy the previous DataTables instance
                  if (jQuery.fn.DataTable.isDataTable('#consult-table')) {
                    jQuery('#consult-table').DataTable().destroy();
                  }

                    console.log(responsePHP.user_dash + ' -2 ' + responsePHP.archived);
                  // Re-initialize DataTables
                  initializeDataTables(responsePHP.user_dash, responsePHP.archived);
                });
                break;
                case 'save_message':
                    call_update_ajax('get_updated_content', data.consult_id, '#consult-canned-responses-', 'get_canned');
                break;
                case 'edit_message':
                    call_update_ajax('get_updated_content', data.consult_id, '#consult-replies-', 'get_replies');
                break;
                case 'delete_message':
                    call_update_ajax('get_updated_content', data.consult_id, '#consult-replies-', 'get_replies');
                break;
                case 'save_edited_canned_response':
                    call_update_ajax('get_updated_content', data.consult_id, '#consult-canned-responses-', 'get_canned');
                break;
                case 'delete_canned_response':
                    call_update_ajax('get_updated_content', data.consult_id, '#consult-canned-responses-', 'get_canned');
                break;
                case 'save_user_signature':
                  // Get the signature element and the save icon button
                  const signatureElement = document.querySelector('#signature-' + data.consult_id);
                  const saveButton = document.querySelector('#save-signature-' + data.consult_id);

                  if (signatureElement && saveButton) {
                    // Create a text element for the success message
                    const successText = document.createElement('span');
                    successText.textContent = 'Signature saved successfully';
                    successText.classList.add('fade-in', 'text-xs', 'text-green'); // Add the desired classes

                    // Append the success text next to the save button
                    saveButton.insertAdjacentElement('afterend', successText);

                    // Fade out the success text after 2 seconds
                    setTimeout(() => {
                      successText.classList.add('hidden');

                      // Remove the success text after the fade-out animation ends
                      successText.addEventListener('animationend', () => {
                        successText.remove();
                      }, { once: true });
                    }, 2000);
                  }
                break;
                case 'notification_read':
                    jQuery('#notification-button').load(location.href + ' #notification-button');
                break;
                case 'all_notification_read':
                    jQuery('#notification-button').load(location.href + ' #notification-button');
                break;
                case 'update_user_profile':
                  // Get the update button from the profile form
                  const updateButton = document.querySelector('#user-profile-form button[type="submit"]');

                  if (updateButton) {
                    // Create a text element for the success message
                    const successText = document.createElement('span');
                    successText.textContent = 'Profile updated successfully';
                    successText.classList.add('fade-in', 'text-xs', 'text-green', 'ml-8'); // Add the desired classes

                    // Append the success text next to the update button
                    updateButton.insertAdjacentElement('afterend', successText);

                    // Fade out the success text after 2 seconds
                    setTimeout(() => {
                      successText.classList.add('hidden');

                      // Remove the success text after the fade-out animation ends
                      successText.addEventListener('animationend', () => {
                        successText.remove();
                      }, { once: true });
                    }, 2000);
                  }
                  break;
                  case 'toggle_archived_consults':
                    jQuery('#loading-icon').show();
                    jQuery('#consult-dock').load(location.href + ' #consult-dock > *', function() {
                      tinymce.remove();
                      // Destroy the previous DataTables instance
                      if (jQuery.fn.DataTable.isDataTable('#consult-table')) {
                        jQuery('#consult-table').DataTable().destroy();
                      }

                        console.log(responsePHP.user_dash + ' -3 ' + responsePHP.archived);
                      // Re-initialize DataTables
                      initializeDataTables(responsePHP.user_dash, responsePHP.archived);
                    });
                    break;
              default:
                console.log(responsePHP);
            }
        },
        error: function(xhr, status, error) {
            // Show error message
            console.log('Error:', status, error);
        },
        complete: function() {
            // Hide loading spinner
        }
    });
}
function call_update_ajax(action, consult_id, element_id, purpose, extra_id = false) {
  jQuery.ajax({
    url: ajax_object.ajax_url,
    data: { action: action, consult_id: consult_id, purpose: purpose, extra_id : extra_id},
    success: function(updatedContent) {
      // Update the content with the fetched data
      jQuery(element_id + consult_id).html(updatedContent);
    },
    error: function() {
      console.log('An error occurred while fetching the updated content');
    }
  });
}
function updateStatus(status, consult_id, current_user){

    var form_data = {
        "status" : status,
        "consult_id": consult_id,
        "submitted_by" : current_user,
        "purpose" : "update_status",
        "action": 'create_consult_message'
    };
    call_ajax(form_data);
}
function updateMod(moderator, consult_id, current_user){

    var form_data = {
        "moderator" : moderator,
        "consult_id": consult_id,
        "submitted_by" : current_user,
        "purpose" : "update_moderator",
        "action": 'create_consult_message'
    };
    call_ajax(form_data);
}
function updateTopic(topic_id, consult_id, child){

    var form_data = {
        "topic_id" : topic_id,
        "consult_id": consult_id,
        "child": child,
        "purpose" : "update_topic",
        "action": 'create_consult_message'
    };
    call_ajax(form_data);
}
function updateAllConsults(consult_id, user_dash, show_archived){
  user_dash = !!user_dash;
  show_archived = !!show_archived;
    var form_data = {
        "consult_id": consult_id,
        "user_dash": user_dash,
        "show_archived": show_archived,
        "purpose" : "refreshConsults",
        "action": 'create_consult_message'
    };
    call_ajax(form_data);
}
function deleteMessage(message_id, consult_id) {

    var form_data = {
        "message_id": message_id,
        "consult_id": consult_id,
        "purpose" : "delete_message",
        "action": 'create_consult_message'
    };
    call_ajax(form_data);

}
function saveEditedCannedResponse(consult_id, response_id, content) {
  var form_data = {
    "response_id": response_id,
    "consult_id": consult_id,
    "response": content,
    "purpose": "save_edited_canned_response",
    "action": 'create_consult_message'
  };
  call_ajax(form_data);
}
function deleteCannedResponse(consult_id, response_id) {

    var form_data = {
        "response_id": response_id,
        "consult_id": consult_id,
        "purpose" : "delete_canned_response",
        "action": 'create_consult_message'
    };
    call_ajax(form_data);

}
function markNotificationAsRead(notification_id, notification_row) {

    var form_data = {
        "notification_id": notification_id,
        "notification_row": notification_row, 
        "purpose" : "notification_read",
        "action": 'create_consult_message'
    };
    call_ajax(form_data);

}
function markAllNotificationsAsRead(notification_id) {

    var form_data = {
        "notification_id": notification_id, 
        "purpose" : "all_notification_read",
        "action": 'create_consult_message'
    };
    call_ajax(form_data);

}
function deleteNote(row_id, history_id, consult_id) {

    var form_data = {
        "consult_id": consult_id,
        "history_id": history_id,
        "row_id": row_id,
        "purpose" : "delete_note",
        "action": 'create_consult_message'
    };
    call_ajax(form_data);

}
function toggleArchivedConsults(user_dash, archived) {
  user_dash = !!user_dash;
  archived = !!archived;
    var data = {
        "user_dash": user_dash,
        "archived": archived, // Send the archived state
        "action": 'toggle_archived_consults' // Adjust as needed
    };
    call_ajax(data);
}
function saveSignature(consult_id, user_id) {
  // Find the signature textarea
  const signatureTextarea = document.querySelector(`#signature-${consult_id}`);

  // Get the signature content
  const signatureContent = signatureTextarea ? signatureTextarea.value : '';

  // Prepare the data to be sent via AJAX
  const data = {
    action: 'save_user_signature',
    consult_id,
    user_id,
    signature: signatureContent
  };

  // Send the AJAX request
  call_ajax(data);
}
function updatePanel(consult_id) {
  tinymce.init({
      selector: '#message' + consult_id,
      height: 400,
      browser_spellcheck: true,
      plugins: 'table link lists help', //image
      toolbar: 'link | undo redo | blocks | bold italic | alignleft aligncentre alignright alignjustify | indent outdent | bullist numlist',
      valid_elements: 'p,div,span,strong,em,u,del,s,strike,sub,sup,br,ul,ol,li,h1,h2,h3,h4,h5,h6,a[href|target|rel],img[src|alt|width|height|title],table,thead,tbody,tfoot,tr,th,td,caption,blockquote',
      /* enable title field in the Image dialog*/
      image_title: true,
      /* enable automatic uploads of images represented by blob or data URIs*/
      automatic_uploads: true,
      /*
        URL of our upload handler (for more details check: https://www.tiny.cloud/docs/configure/file-image-upload/#images_upload_url)
        images_upload_url: 'postAcceptor.php',
        here we add custom filepicker only to Image dialog
      */
      file_picker_types: 'image',
      /* and here's our custom image picker*/
      file_picker_callback: (cb, value, meta) => {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');

        input.addEventListener('change', (e) => {
          const file = e.target.files[0];

          const reader = new FileReader();
          reader.addEventListener('load', () => {
            /*
              Note: Now we need to register the blob in TinyMCEs image blob
              registry. In the next release this part hopefully won't be
              necessary, as we are looking to handle it internally.
            */
            const id = 'blobid' + (new Date()).getTime();
            const blobCache =  tinymce.activeEditor.editorUpload.blobCache;
            const base64 = reader.result.split(',')[1];
            const blobInfo = blobCache.create(id, file, base64);
            blobCache.add(blobInfo);

            /* call the callback and populate the Title field with the file name */
            cb(blobInfo.blobUri(), { title: file.name });
          });
          reader.readAsDataURL(file);
        });

        input.click();
      },
  });
}
function editMessage(message_id, consult_id, submitted_by, moderators_assigned) {
  // Get the message element to be edited
  var messageElement = document.querySelector('#consult-' + consult_id + '-message-' + message_id);

  // Check if the message element already contains a tinymce editor
  var existingEditor = messageElement.querySelector('textarea[data-editor]');
  
  if (existingEditor) {
    if (window.currentPopup === 'editMessage') {
      closeEditScreens();
      window.currentPopup = null;
    }
    return; // Editor is already open, do nothing
  }

  if (window.currentPopup === 'editMessage') {
    closeEditScreens();
  }
  window.currentPopup = 'editMessage';
  
  // Get the current message text
  var messageText = messageElement.innerHTML;
  
  // Create a new form element to hold the tinymce editor and submit button
  var formElement = document.createElement('form');
  formElement.setAttribute('id', 'consult-' + consult_id + '-message-' + message_id + '-form');
  messageElement.innerHTML = '';
  messageElement.appendChild(formElement);
  
  // Create a new textarea element to hold the tinymce editor
  var textareaElement = document.createElement('textarea');
  var editorId = 'consult-' + consult_id + '-message-' + message_id + '-editor';
  textareaElement.setAttribute('id', editorId);
  textareaElement.setAttribute('data-editor', true);
  textareaElement.textContent = messageText;
  formElement.appendChild(textareaElement);

  // Create hidden input fields for consult_id, submitted_by, and moderators_assigned
  var consultIdInput = document.createElement('input');
  consultIdInput.setAttribute('type', 'hidden');
  consultIdInput.setAttribute('name', 'consult_id');
  consultIdInput.setAttribute('value', consult_id);
  formElement.appendChild(consultIdInput);

  var submittedByInput = document.createElement('input');
  submittedByInput.setAttribute('type', 'hidden');
  submittedByInput.setAttribute('name', 'submitted_by');
  submittedByInput.setAttribute('value', submitted_by);
  formElement.appendChild(submittedByInput);

  var moderatorsAssignedInput = document.createElement('input');
  moderatorsAssignedInput.setAttribute('type', 'hidden');
  moderatorsAssignedInput.setAttribute('name', 'moderators_assigned');
  moderatorsAssignedInput.setAttribute('value', moderators_assigned);
  formElement.appendChild(moderatorsAssignedInput);
  
  // Create a submit button to save the edited message
  var submitButton = document.createElement('button');
  submitButton.textContent = 'Submit';
  submitButton.setAttribute('class', 'border rounded uppercase tracking-wide font-bold text-xs mr-4 px-6 py-3 cursor-pointer mt-4 text-blue bg-white hover:border-gray-400 focus:border-gray-400 transition-all duration-200 ease-in-out transform focus:scale-105 hover:scale-105');
  formElement.appendChild(submitButton);

  // Create a cancel button to restore the original message text
  var cancelButton = document.createElement('button');
  cancelButton.textContent = 'Cancel';
  cancelButton.setAttribute('class', 'text-text font-bold py-2 text-sm hover:text-pink focus:text-pink inline-block transition-all duration-200 ease-in-out');
  cancelButton.addEventListener('click', function () {
    var editorInstance = tinymce.get(editorId); // Get the editor instance
    editorInstance.remove(); // remove the editor instance
    messageElement.innerHTML = '';
    messageElement.innerHTML = messageText; // set the message text to the original message
  });
  formElement.appendChild(cancelButton);
  
  // Initialize the tinymce editor
  var editor = tinymce.init({
    selector: '#' + editorId,
    height: 400,
    browser_spellcheck: true,
    plugins: 'table image link lists help',
    toolbar: 'image link | undo redo | blocks | bold italic | alignleft aligncentre alignright alignjustify | indent outdent | bullist numlist',
    valid_elements: 'p,div,span,strong,em,u,del,s,strike,sub,sup,br,ul,ol,li,h1,h2,h3,h4,h5,h6,a[href|target|rel],img[src|alt|width|height|title],table,thead,tbody,tfoot,tr,th,td,caption,blockquote',
    setup: function (editor) {
      editor.on('init', function () {
        // Set the focus to the editor
        editor.focus();
      });
    },
    /* enable title field in the Image dialog*/
    image_title: true,
    /* enable automatic uploads of images represented by blob or data URIs*/
    automatic_uploads: true,
    /*
      URL of our upload handler (for more details check: https://www.tiny.cloud/docs/configure/file-image-upload/#images_upload_url)
      images_upload_url: 'postAcceptor.php',
      here we add custom filepicker only to Image dialog
    */
    file_picker_types: 'image',
    /* and here's our custom image picker*/
    file_picker_callback: (cb, value, meta) => {
      const input = document.createElement('input');
      input.setAttribute('type', 'file');
      input.setAttribute('accept', 'image/*');

      input.addEventListener('change', (e) => {
        const file = e.target.files[0];

        const reader = new FileReader();
        reader.addEventListener('load', () => {
          /*
            Note: Now we need to register the blob in TinyMCEs image blob
            registry. In the next release this part hopefully won't be
            necessary, as we are looking to handle it internally.
          */
          const id = 'blobid' + (new Date()).getTime();
          const blobCache =  tinymce.activeEditor.editorUpload.blobCache;
          const base64 = reader.result.split(',')[1];
          const blobInfo = blobCache.create(id, file, base64);
          blobCache.add(blobInfo);

          /* call the callback and populate the Title field with the file name */
          cb(blobInfo.blobUri(), { title: file.name });
        });
        reader.readAsDataURL(file);
      });

      input.click();
    },
  });
}
function editCannedResponse(consult_id, response_id) {
  var responseElement = document.querySelector('#consult-' + consult_id + '-canned-response-' + response_id);

  var existingEditor = responseElement.querySelector('textarea[data-editor]');

  if (existingEditor) {
    if (window.currentPopup === 'editCannedResponse') {
      closeEditScreens();
      window.currentPopup = null;
    }
    return;
  }

  if (window.currentPopup === 'editCannedResponse') {
    closeEditScreens();
  }
  window.currentPopup = 'editCannedResponse';

  var responseText = responseElement.innerHTML;

  var formElement = document.createElement('form');
  formElement.setAttribute('id', 'consult-' + consult_id + '-canned-response-' + response_id + '-form');
  responseElement.innerHTML = '';
  responseElement.appendChild(formElement);

  var textareaElement = document.createElement('textarea');
  var editorId = 'consult-' + consult_id + '-canned-response-' + response_id + '-editor';
  textareaElement.setAttribute('id', editorId);
  textareaElement.setAttribute('data-editor', true);
  textareaElement.textContent = responseText;
  formElement.appendChild(textareaElement);

  var submitButton = document.createElement('button');
  submitButton.textContent = 'Submit';
  submitButton.setAttribute('type', 'submit');
  submitButton.setAttribute('class', 'border rounded uppercase tracking-wide font-bold text-xs mr-4 px-6 py-3 cursor-pointer mt-4 text-blue bg-white hover:border-gray-400 focus:border-gray-400 transition-all duration-200 ease-in-out transform focus:scale-105 hover:scale-105');
  formElement.appendChild(submitButton);

  var cancelButton = document.createElement('button');
  cancelButton.textContent = 'Cancel';
  cancelButton.setAttribute('type', 'button');
  cancelButton.setAttribute('class', 'text-text font-bold py-2 text-sm hover:text-pink focus:text-pink inline-block transition-all duration-200 ease-in-out');
  cancelButton.addEventListener('click', function () {
    tinymce.remove('#' + editorId);
    responseElement.innerHTML = '';
    responseElement.innerHTML = responseText;
  });
  formElement.appendChild(cancelButton);

  tinymce.init({
    selector: '#' + editorId,
    height: 400,
    browser_spellcheck: true,
    plugins: 'table image link lists help',
    toolbar: 'image link | undo redo | blocks | bold italic | alignleft aligncentre alignright alignjustify | indent outdent | bullist numlist',
    valid_elements: 'p,div,span,strong,em,u,del,s,strike,sub,sup,br,ul,ol,li,h1,h2,h3,h4,h5,h6,a[href|target|rel],img[src|alt|width|height|title],table,thead,tbody,tfoot,tr,th,td,caption,blockquote',
    setup: function (editor) {
      editor.on('init', function () {
        editor.focus();
      });
    },
    /* enable title field in the Image dialog*/
    image_title: true,
    /* enable automatic uploads of images represented by blob or data URIs*/
    automatic_uploads: true,
    /*
      URL of our upload handler (for more details check: https://www.tiny.cloud/docs/configure/file-image-upload/#images_upload_url)
      images_upload_url: 'postAcceptor.php',
      here we add custom filepicker only to Image dialog
    */
    file_picker_types: 'image',
    /* and here's our custom image picker*/
    file_picker_callback: (cb, value, meta) => {
      const input = document.createElement('input');
      input.setAttribute('type', 'file');
      input.setAttribute('accept', 'image/*');

      input.addEventListener('change', (e) => {
        const file = e.target.files[0];

        const reader = new FileReader();
        reader.addEventListener('load', () => {
          /*
            Note: Now we need to register the blob in TinyMCEs image blob
            registry. In the next release this part hopefully won't be
            necessary, as we are looking to handle it internally.
          */
          const id = 'blobid' + (new Date()).getTime();
          const blobCache =  tinymce.activeEditor.editorUpload.blobCache;
          const base64 = reader.result.split(',')[1];
          const blobInfo = blobCache.create(id, file, base64);
          blobCache.add(blobInfo);

          /* call the callback and populate the Title field with the file name */
          cb(blobInfo.blobUri(), { title: file.name });
        });
        reader.readAsDataURL(file);
      });

      input.click();
    },
  });

  formElement.addEventListener('submit', function (event) {
    event.preventDefault();

    var editor = tinymce.get(editorId);
    var content = editor.getContent();
    

    if (!content) {
      alert('Please fill in the required response field!');
      return;
    }

    saveEditedCannedResponse(consult_id, response_id, content);

    tinymce.remove('#' + editorId);
    responseElement.innerHTML = '';
    responseElement.textContent = content;
  });
}
const statusColors = {
  'Open': 'green',
  'In Progress': 'blue',
  'Closed': 'pink',
};
function statusColor(statusLabel) {
  return statusColors[statusLabel] || 'green';
}
function closeEditScreens() {
    document.querySelectorAll('textarea[data-editor]').forEach(function (editor) {
        var editorElement = editor.parentElement;
        var originalText = editor.textContent;
        editor.remove();
        editorElement.textContent = originalText;
    });
}
function addToEditor(consult_id, response_id) {
  // Find the selected response element
  const selectedResponse = document.querySelector(`#consult-${consult_id}-canned-response-${response_id}`);

  // Get the response content
  const responseContent = selectedResponse ? selectedResponse.innerHTML : '';

  // Get the editor instance
  const editor = tinymce.get(`message${consult_id}`);

  // Check if the editor instance exists
  if (!editor) {
    console.error(`Editor with ID "message${consult_id}" not found.`);
    return;
  }

  // Insert the response content into the editor at the current cursor position
  editor.insertContent(responseContent);
}
//To Delete
function addSignatureToEditor(consult_id, user_id) {
  // Find the selected signature textarea
  const signatureTextarea = document.querySelector(`#signature-${consult_id}`);

  // Get the signature content
  const signatureContent = signatureTextarea ? signatureTextarea.value : '';

  // Replace line breaks with HTML line breaks
  const signatureHTML = signatureContent.replace(/\n/g, '<br>');

  // Get the editor instance
  const editor = tinymce.get(`message${consult_id}`);

  // Check if the editor instance exists
  if (!editor) {
    console.error(`Editor with ID "message${consult_id}" not found.`);
    return;
  }

  // Insert the signature content into the editor at the current cursor position
  editor.insertContent(signatureHTML);
}

function initializeDataTables(user_dash, show_archived) {
  jQuery('#consult-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: {
      url: ajax_object.ajax_url, // Replace with your server-side processing URL
      type: 'POST',
      data: {
          action: 'lazy_load_table_posts', // This corresponds to the action name in PHP
          user_dash: user_dash,
          show_archived: show_archived
        }
    },
    createdRow: function (row, data, dataIndex) {
      // Extract the consult_id from the data (assuming it's in the first column)
      var consult_id = data[0];

      // Modify the tr element to match your desired structure
      jQuery(row).addClass('slideover-btn cursor-pointer')
        .attr('x-data', '{ isOpen: false, activeConsult: null, openSlideover(consultId) { this.activeConsult = consultId; this.isOpen = true; } }')
        .attr('x-on:click', 'if ($event.target.closest(\'.slideover-btn\')) { openSlideover($event.target.closest(\'.slideover-btn\').dataset.consultId); updatePanel($event.target.closest(\'.slideover-btn\').dataset.consultId); }')
        .attr('data-consult-id', consult_id);

      jQuery('td', row).addClass('px-6 py-4 whitespace-nowrap text-text text-sm');
      // Add the "font-bold" class to the first td element
      jQuery('td:first', row).addClass('font-bold');
    },
    drawCallback: function(settings) {
      var api = this.api();
      var slideover = '';

      // Remove the previously added slideover panels (assuming a class name)
      jQuery('.slideover-panel').remove();

      api.rows({page:'current'}).every(function(rowIdx, tableLoop, rowLoop) {
        var row = this.row(rowIdx);
        var consult_id = row.data()[0]; // Adjust the index to get the correct consult_id
        var slideover_html = row.data().slideover_html;

        // Build the slideover HTML for this consult_id, similar to the existing $slideover code
        slideover = slideover_html;

      });

      // Append the generated slideover panels right after the table
      jQuery('#consult-table').after(slideover);
    },
    columns: [
        { title: 'Consult ID', orderable: false },
        { title: 'Title', orderable: false },
        { title: 'Date Created', orderable: false },
        { title: user_dash ? 'Moderator Assigned' : 'Submitted By', orderable: false }, // Adjust the condition as needed
        { title: 'Status', orderable: false },
        { title: 'Topic', orderable: false }
    ],
    language: {
      lengthMenu: 'Display _MENU_ consults',
      info: 'Showing _START_ to _END_ of _TOTAL_ consults',
      infoEmpty: 'No consults to show',
      zeroRecords: 'No consults found'
    },
    stateSave: true,
    responsive: {
      breakpoints: [
        { name: 'tablet', width: 768 },
        { name: 'phone', width: 576 }
      ],
      details: {
        type: 'column',
        target: 'tr'
      }
    },
    columnDefs: [
      { type: 'status', targets: 4 },
      { type: 'date', targets: 2 },
      { responsivePriority: 1, targets: 0 },
      { responsivePriority: 2, targets: 1 },
      { responsivePriority: 3, targets: 2 },
      { responsivePriority: 4, targets: 3 },
      { responsivePriority: 5, targets: 4 }
    ],
    scrollX: true, // Enable horizontal scrolling
    scrollCollapse: true, // Collapse the table when scrolling horizontally
    fixedColumns: true, // Enable fixed columns
    fixedColumns: {
      leftColumns: 1 // Set the number of left-fixed columns (adjust as needed)
    },
    initComplete: function(settings, json) {
      jQuery('#loading-icon').hide();
    },
  });
}
