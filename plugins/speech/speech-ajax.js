jQuery(document).ready(function($) {
  function updateTable() {
    var category = $('#speech-category').val();
    var timeRange = $('#speech-time-range').val();

    $.ajax({
      url: speech_ajax_object.ajax_url,
      type: 'POST',
      data: {
        action: 'speech_update_table',
        category: category,
        time_range: timeRange, // Pass the time range value
      },
      success: function(response) {
        if (response.success) {
          $('#speech-table tbody').html(response.data);
        }
      }
    });
  }

    // Update table on filter change
    $('#speech-category, #speech-time-range').on('change', function() {
        updateTable();
    });

  updateTable(); // Initial table update

  setInterval(updateTable, speech_ajax_object.interval); // Periodic table updates
});
