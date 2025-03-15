$(document).on("rex:ready", function () {
  // check if page contains editable content, if not stop
  if (document.getElementsByClassName("rex-slices").length == 0) {
    console.log("no editable slice found");
  } else {
    var number_columns = 1;
    var min_width_column = 1;
    var store = {};

    console.log("initialising columns...");

    number_columns = rex.number_columns;
    min_width_column = rex.min_width_column;

    var element = document.getElementsByClassName("rex-slices")[0];
    // var element = document.getElementsByClassName("dragdrop")[0];

    var list = Sortable.create(element, {
      // handle: ".slice_columns_handler",
      handle: ".panel-heading",
      dataIdAttr: "data-slice-id",
      animation: 150,
      ghostClass: "slice_columns_ghost_class",
      onChange: function (evt) {
        // console.log("old: " + evt.oldDraggableIndex);
        // console.log("new: " + evt.newDraggableIndex);
        // console.log("Order:");
        // console.log(list.toArray());
        // console.log("Widths:");
        // console.log(store);

        let h = document.getElementsByClassName("dragdrop")[0];
        var article_id = h.getAttribute("data-article-id");
        var clang_id = h.getAttribute("data-clang-id");

        $.post(
          "index.php?page=content/edit&rex-api-call=sorter",
          {
            function: "updateorder",
            order: JSON.stringify(list.toArray()),
            article: article_id,
            clang: clang_id,
          },
          function (result) {
            console.log(result);
          }
        );

        // console.log(evt.item.getAttribute("data-slice-id"));
      },
    });

    // init structure
    // a = list.toArray();
    // // console.log(a);
    // for (var i = 0; i < a.length; i++) {
    //   store[a[i]] = "6/6";
    // }
    // console.log(store);

    const btns_wider = document.getElementsByClassName("btn_wider");
    const btns_smaler = document.getElementsByClassName("btn_smaller");

    for (var i = 0; i < btns_smaler.length; i++) {
      btns_smaler[i].addEventListener("click", smaller);
    }
    for (var i = 0; i < btns_wider.length; i++) {
      btns_wider[i].addEventListener("click", wider);
    }

    // Section-Funktionalität
    $(".btn_add_to_section").click(function() {
      let parent = $(this).closest(".dragdrop");
      let sliceId = parent.attr("data-slice-id");
      let articleId = parent.attr("data-article-id");
      let clangId = parent.attr("data-clang-id");
      
      // Section-Modal erstellen/anzeigen
      let modal = $('<div class="modal fade" tabindex="-1" role="dialog">' +
          '<div class="modal-dialog" role="document">' +
          '<div class="modal-content">' +
          '<div class="modal-header">' +
          '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
          '<h4 class="modal-title">Slice zu Section hinzufügen</h4>' +
          '</div>' +
          '<div class="modal-body">' +
          '<div class="form-group">' +
          '<label for="section_id">Bestehende Section auswählen</label>' +
          '<select class="form-control" id="section_id">' +
          '<option value="0">Neue Section erstellen</option>' +
          '</select>' +
          '</div>' +
          '</div>' +
          '<div class="modal-footer">' +
          '<button type="button" class="btn btn-default" data-dismiss="modal">Abbrechen</button>' +
          '<button type="button" class="btn btn-primary" id="save-section">Speichern</button>' +
          '</div>' +
          '</div>' +
          '</div>' +
          '</div>');
      
      // Bestehende Sections laden
      $.ajax({
        url: "index.php?page=content/edit&rex-api-call=section&function=get_sections&article_id=" + articleId + "&clang=" + clangId,
        success: function(result) {
          try {
            let sections = JSON.parse(result);
            for (let sectionId in sections) {
              modal.find("#section_id").append('<option value="' + sectionId + '">Section ' + sectionId + '</option>');
            }
          } catch (e) {
            console.error("Fehler beim Parsen der Sections:", e);
          }
        }
      });
      
      $('body').append(modal);
      modal.modal('show');
      
      modal.find("#save-section").click(function() {
        let sectionId = modal.find("#section_id").val();
        
        // Slice zur Section hinzufügen
        $.ajax({
          url: "index.php?page=content/edit&rex-api-call=section&function=add_to_section&slice_id=" + sliceId + "&article_id=" + articleId + "&clang=" + clangId + "&section_id=" + sectionId,
          success: function(result) {
            try {
              let response = JSON.parse(result);
              if (response.status === 'success') {
                // UI aktualisieren
                parent.addClass('in-section section-' + response.section_id);
                parent.attr('data-section-id', response.section_id);
                modal.modal('hide');
                window.location.reload(); // Einfache Lösung: Seite neu laden
              }
            } catch (e) {
              console.error("Fehler beim Verarbeiten der Antwort:", e);
            }
          }
        });
      });
      
      // Modal-Cleanup nach dem Schließen
      modal.on('hidden.bs.modal', function () {
        modal.remove();
      });
    });
    
    // Menü zum Entfernen von Slices aus Sections hinzufügen
    $(".in-section").each(function() {
      let sliceId = $(this).attr("data-slice-id");
      let articleId = $(this).attr("data-article-id");
      let clangId = $(this).attr("data-clang-id");
      
      // "Aus Section entfernen"-Button zum Kontextmenü hinzufügen
      $(this).find(".panel-heading .rex-panel-options").append(
        '<button class="btn btn-default btn-xs remove-from-section" ' +
        'data-slice-id="' + sliceId + '" ' +
        'data-article-id="' + articleId + '" ' +
        'data-clang-id="' + clangId + '">' +
        '<i class="fa fa-object-ungroup"></i> Aus Section entfernen</button>'
      );
    });
    
    // Event-Handler für "Aus Section entfernen"
    $(document).on("click", ".remove-from-section", function() {
      let sliceId = $(this).attr("data-slice-id");
      let articleId = $(this).attr("data-article-id");
      let clangId = $(this).attr("data-clang-id");
      
      if (confirm("Möchtest du diesen Slice wirklich aus der Section entfernen?")) {
        // Slice aus Section entfernen
        $.ajax({
          url: "index.php?page=content/edit&rex-api-call=section&function=remove_from_section&slice_id=" + sliceId + "&article_id=" + articleId + "&clang=" + clangId,
          success: function(result) {
            try {
              let response = JSON.parse(result);
              if (response.status === 'success') {
                // UI aktualisieren
                window.location.reload(); // Einfache Lösung: Seite neu laden
              }
            } catch (e) {
              console.error("Fehler beim Verarbeiten der Antwort:", e);
            }
          }
        });
      }
    });
    
    // Section-spezifische Drag & Drop-Konfiguration
    // Erlaubt das Ziehen ganzer Sections
    $(".slice-section").each(function() {
      let sectionId = $(this).attr("data-section-id");
      
      // Füge einen Abschnitt-Header hinzu
      $(this).prepend(
        '<div class="section-header">' +
        '<span class="section-title">Section ' + sectionId + '</span>' +
        '<div class="section-controls">' +
        '<button class="btn btn-xs btn-default section-settings" data-section-id="' + sectionId + '">' +
        '<i class="fa fa-cog"></i> Einstellungen</button>' +
        '</div>' +
        '</div>'
      );
    });
    
    // Event-Handler für Section-Einstellungen
    $(document).on("click", ".section-settings", function() {
      let sectionId = $(this).attr("data-section-id");
      
      // Section-Einstellungen-Modal erstellen/anzeigen
      let modal = $('<div class="modal fade" tabindex="-1" role="dialog">' +
          '<div class="modal-dialog" role="document">' +
          '<div class="modal-content">' +
          '<div class="modal-header">' +
          '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
          '<h4 class="modal-title">Section-Einstellungen</h4>' +
          '</div>' +
          '<div class="modal-body">' +
          '<div class="form-group">' +
          '<label for="section_class">CSS-Klasse</label>' +
          '<input type="text" class="form-control" id="section_class" placeholder="CSS-Klasse für diese Section">' +
          '</div>' +
          '<div class="form-group">' +
          '<label for="section_background">Hintergrundfarbe</label>' +
          '<input type="color" class="form-control" id="section_background">' +
          '</div>' +
          '</div>' +
          '<div class="modal-footer">' +
          '<button type="button" class="btn btn-default" data-dismiss="modal">Abbrechen</button>' +
          '<button type="button" class="btn btn-primary" id="save-section-settings">Speichern</button>' +
          '</div>' +
          '</div>' +
          '</div>' +
          '</div>');
      
      // Bestehende Einstellungen laden
      $.ajax({
        url: "index.php?page=content/edit&rex-api-call=section&function=get_section_settings&section_id=" + sectionId,
        success: function(result) {
          try {
            let settings = JSON.parse(result);
            modal.find("#section_class").val(settings.class || '');
            modal.find("#section_background").val(settings.background || '#ffffff');
          } catch (e) {
            console.error("Fehler beim Parsen der Section-Einstellungen:", e);
          }
        }
      });
      
      $('body').append(modal);
      modal.modal('show');
      
      modal.find("#save-section-settings").click(function() {
        let sectionClass = modal.find("#section_class").val();
        let sectionBackground = modal.find("#section_background").val();
        
        // Section-Einstellungen speichern
        $.ajax({
          url: "index.php?page=content/edit&rex-api-call=section&function=save_section_settings",
          method: "POST",
          data: {
            section_id: sectionId,
            section_class: sectionClass,
            section_background: sectionBackground
          },
          success: function(result) {
            try {
              let response = JSON.parse(result);
              if (response.status === 'success') {
                modal.modal('hide');
                window.location.reload();
              }
            } catch (e) {
              console.error("Fehler beim Verarbeiten der Antwort:", e);
            }
          }
        });
      });
      
      // Modal-Cleanup nach dem Schließen
      modal.on('hidden.bs.modal', function () {
        modal.remove();
      });
    });
  }

  function smaller(el) {
    target = el.target;
    // let parent = target.closest(".rex-slice");
    let parent = target.closest(".dragdrop");

    let attr_width = parseInt(parent.getAttribute("data-width"));

    if (!(attr_width - rex.slicesteps < min_width_column)) {
      width = 100 * ((attr_width - rex.slicesteps) / number_columns) + "%";

      parent.style.width = width;
      slice_id = parent.getAttribute("data-slice-id");
      article_id = parent.getAttribute("data-article-id");
      var clang_id = parent.getAttribute("data-clang-id");

      // update data-width attribute
      parent.setAttribute(
        "data-width",
        parseInt(parent.getAttribute("data-width")) - rex.slicesteps
      );

      $.post(
        "index.php?page=content/edit&rex-api-call=sorter",
        {
          function: "updatewidth",
          slice: slice_id,
          article: article_id,
          clang: clang_id,
          width: parent.getAttribute("data-width"),
        },
        function (result) {
          console.log(result);
        }
      );
    }
  }

  function wider(el) {
    target = el.target;
    // let parent = target.closest(".rex-slice");
    let parent = target.closest(".dragdrop");

    let attr_width = parseInt(parent.getAttribute("data-width"));

    if (!(attr_width + rex.slicesteps > number_columns)) {
      width = 100 * ((attr_width + rex.slicesteps) / number_columns) + "%";

      if (event.shiftKey) {
        width = 100 + "%";
      }
      parent.style.width = width;
      slice_id = parent.getAttribute("data-slice-id");
      article_id = parent.getAttribute("data-article-id");
      var clang_id = parent.getAttribute("data-clang-id");

      if (event.shiftKey) {
        parent.setAttribute("data-width", number_columns);
      } else {
        // update data-width attribute
        parent.setAttribute(
          "data-width",
          parseInt(parent.getAttribute("data-width")) + rex.slicesteps
        );
      }

      $.post(
        "index.php?page=content/edit&rex-api-call=sorter",
        {
          function: "updatewidth",
          slice: slice_id,
          article: article_id,
          clang: clang_id,
          width: parent.getAttribute("data-width"),
        },
        function (result) {
          console.log(result);
        }
      );
    }
  }
});
