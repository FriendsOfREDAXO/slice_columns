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

    var list = Sortable.create(element, {
      handle: ".panel-heading",
      dataIdAttr: "data-slice-id",
      animation: 150,
      ghostClass: "slice_columns_ghost_class",
      onChange: function (evt) {
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
      },
    });

    const btns_wider = document.getElementsByClassName("btn_wider");
    const btns_smaler = document.getElementsByClassName("btn_smaller");

    for (var i = 0; i < btns_smaler.length; i++) {
      btns_smaler[i].addEventListener("click", smaller);
    }
    for (var i = 0; i < btns_wider.length; i++) {
      btns_wider[i].addEventListener("click", wider);
    }

    // Einfacheres Hinzufügen zu Section
    $(document).on("click", ".btn_add_to_section", function(e) {
      e.preventDefault();
      console.log("Add to section button clicked");
      
      let parent = $(this).closest(".dragdrop");
      let sliceId = parent.attr("data-slice-id");
      let articleId = parent.attr("data-article-id");
      let clangId = parent.attr("data-clang-id");
      
      // Einfachere Version - frage direkt nach der Section-ID
      let sectionId = prompt("Section-ID eingeben (leer lassen für neue Section):", "");
      
      if (sectionId !== null) { // Wenn nicht abgebrochen
        sectionId = sectionId.trim() === "" ? 0 : parseInt(sectionId);
        
        // Slice zur Section hinzufügen
        $.ajax({
          url: "index.php?page=content/edit&rex-api-call=section&function=add_to_section",
          data: {
            slice_id: sliceId,
            article_id: articleId,
            clang: clangId,
            section_id: sectionId
          },
          success: function(result) {
            console.log("Section API response:", result);
            try {
              let response = JSON.parse(result);
              if (response.status === 'success') {
                // Seite neu laden
                window.location.reload();
              } else {
                alert("Fehler: " + response.message);
              }
            } catch (e) {
              console.error("Fehler beim Parsen der API-Antwort:", e, result);
              alert("Fehler bei der Kommunikation mit dem Server.");
            }
          },
          error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            alert("Fehler bei der Kommunikation mit dem Server: " + error);
          }
        });
      }
    });
    
    // Entfernen aus Section
    $(document).on("click", ".btn_remove_from_section", function(e) {
      e.preventDefault();
      console.log("Remove from section button clicked");
      
      if (confirm("Möchtest du diesen Slice wirklich aus der Section entfernen?")) {
        let parent = $(this).closest(".dragdrop");
        let sliceId = parent.attr("data-slice-id");
        let articleId = parent.attr("data-article-id");
        let clangId = parent.attr("data-clang-id");
        
        $.ajax({
          url: "index.php?page=content/edit&rex-api-call=section&function=remove_from_section",
          data: {
            slice_id: sliceId,
            article_id: articleId,
            clang: clangId
          },
          success: function(result) {
            console.log("Section API response:", result);
            try {
              let response = JSON.parse(result);
              if (response.status === 'success') {
                // Seite neu laden
                window.location.reload();
              } else {
                alert("Fehler: " + response.message);
              }
            } catch (e) {
              console.error("Fehler beim Parsen der API-Antwort:", e, result);
              alert("Fehler bei der Kommunikation mit dem Server.");
            }
          },
          error: function(xhr, status, error) {
            console.error("AJAX Error:", status, error);
            alert("Fehler bei der Kommunikation mit dem Server: " + error);
          }
        });
      }
    });
  }

  function smaller(el) {
    target = el.target;
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
