$(document).on("rex:ready", function () {
  // check if page contains editable content, if not stop
  if (document.getElementsByClassName("rex-slices").length == 0) {
    console.log("no editable slice found");
  } else {
    var number_columns = 1;
    var min_width_column = 1;
    var store = {};

    console.log("initialising columns with bloecks integration (bloecks is required)...");

    number_columns = rex.number_columns;
    min_width_column = rex.min_width_column;

    // Drag & Drop functionality is handled by bloecks addon (required dependency)
    // var element = document.getElementsByClassName("rex-slices")[0];
    // var list = Sortable.create(element, { ... });

    // init structure - drag & drop related code removed

    const btns_wider = document.getElementsByClassName("btn_wider");
    const btns_smaler = document.getElementsByClassName("btn_smaller");

    for (var i = 0; i < btns_smaler.length; i++) {
      btns_smaler[i].addEventListener("click", smaller);
    }
    for (var i = 0; i < btns_wider.length; i++) {
      btns_wider[i].addEventListener("click", wider);
    }
    
    // Update button icons based on current width
    updateButtonIcons();
  }

  function smaller(el) {
    target = el.target;
    // Support both regular slice-column and bloecks wrapper structure
    let parent = target.closest(".slice-column") || target.closest(".bloecks-dragdrop.slice-column");

    let attr_width = parseInt(parent.getAttribute("data-width"));
    
    // Prüfe ob es reset (0) ist - dann zur maximalen Breite
    if (attr_width === 0) {
      attr_width = number_columns + rex.slicesteps; // Setze auf max + 1, damit die normale Logik greift
    }

    if (!(attr_width - rex.slicesteps < min_width_column)) {
      let newWidth = attr_width - rex.slicesteps;
      width = 100 * (newWidth / number_columns) + "%";

      parent.style.width = width;
      slice_id = parent.getAttribute("data-slice-id");
      article_id = parent.getAttribute("data-article-id");
      var clang_id = parent.getAttribute("data-clang-id");

      // update data-width attribute
      parent.setAttribute("data-width", newWidth);

      $.post(
        "index.php?page=content/edit&rex-api-call=sorter",
        {
          function: "updatewidth",
          slice: slice_id,
          article: article_id,
          clang: clang_id,
          width: newWidth,
        },
        function (result) {
          console.log(result);
          updateButtonIcons();
        }
      );
    }
  }

  function wider(event) {
    target = event.target;
    // Support both regular slice-column and bloecks wrapper structure
    let parent = target.closest(".slice-column") || target.closest(".bloecks-dragdrop.slice-column");

    let attr_width = parseInt(parent.getAttribute("data-width"));
    slice_id = parent.getAttribute("data-slice-id");
    article_id = parent.getAttribute("data-article-id");
    var clang_id = parent.getAttribute("data-clang-id");
    
    let newWidth;

    if (event.shiftKey) {
      newWidth = number_columns;
      width = 100 * (newWidth / number_columns) + "%";
      parent.style.width = width;
    } else {
      // Prüfe ob wir bereits bei maximaler Breite sind
      if (attr_width >= number_columns) {
        // Bei maximaler Breite -> Reset (0 = kein Wrapper)
        newWidth = 0;
        // Im Backend: Reset soll wie max aussehen (volle Breite)
        parent.style.width = "100%";
      } else {
        // Normal vergrößern
        newWidth = attr_width + rex.slicesteps;
        // Prüfe Maximum
        if (newWidth > number_columns) {
          newWidth = number_columns;
        }
        width = 100 * (newWidth / number_columns) + "%";
        parent.style.width = width;
      }
    }

    // update data-width attribute
    parent.setAttribute("data-width", newWidth);

    $.post(
      "index.php?page=content/edit&rex-api-call=sorter",
      {
        function: "updatewidth",
        slice: slice_id,
        article: article_id,
        clang: clang_id,
        width: newWidth,
      },
      function (result) {
        console.log(result);
        updateButtonIcons();
      }
    );
  }

  function updateButtonIcons() {
    // Aktualisiere alle Button-Icons basierend auf der aktuellen Breite
    const widerBtns = document.querySelectorAll('.btn_wider');
    const smallerBtns = document.querySelectorAll('.btn_smaller');
    
    widerBtns.forEach(btn => {
      let parent = btn.closest(".slice-column") || btn.closest(".bloecks-dragdrop.slice-column");
      if (parent) {
        let attr_width = parseInt(parent.getAttribute("data-width"));
        let icon = btn.querySelector('i');
        
        if (attr_width >= number_columns) {
          // Bei maximaler Breite -> zeige Reset-Icon
          icon.className = 'fa fa-lg fa-times-circle slice_columns_icon';
          btn.title = 'Wrapper entfernen';
        } else if (attr_width === 0) {
          // Bei Reset-Zustand -> zeige normales Expand-Icon
          icon.className = 'fa fa-lg fa-expand slice_columns_icon';
          btn.title = 'Breiter machen';
        } else {
          // Normal -> zeige Expand-Icon
          icon.className = 'fa fa-lg fa-expand slice_columns_icon';
          btn.title = 'Breiter machen';
        }
      }
    });
    
    smallerBtns.forEach(btn => {
      let parent = btn.closest(".slice-column") || btn.closest(".bloecks-dragdrop.slice-column");
      if (parent) {
        let attr_width = parseInt(parent.getAttribute("data-width"));
        let icon = btn.querySelector('i');
        
        if (attr_width === 0) {
          // Bei Reset-Zustand -> zeige "zurück zur normalen Größe"
          icon.className = 'fa fa-lg fa-compress slice_columns_icon';
          btn.title = 'Normale Größe';
        } else {
          // Normal -> zeige Compress-Icon
          icon.className = 'fa fa-lg fa-compress slice_columns_icon';
          btn.title = 'Schmaler machen';
        }
      }
    });
  }
});
