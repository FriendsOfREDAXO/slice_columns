$(document).on("rex:ready", function () {
  // check if page contains editable content, if not stop
  if (document.getElementsByClassName("rex-slices").length == 0) {
    console.log("no editable slice found");
  } else {
    var number_columns = 1;
    var min_width_column = 1;
    var store = {};

    console.log("initialising columns...");

	  // get number of columns from addon settings
    $.get(
      "/index.php?rex-api-call=slice_columns_helper",
      { function: "get_config" },
      function (result) {
        result = JSON.parse(result);
        console.log(result);

        number_columns = result.number_columns;
        min_width_column = result.min_width_column;
      }
    );  
	  
	  
	// number_columns = rex.number_columns;
    // min_width_column = rex.min_width_column;


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

        $.post(
          "/index.php?rex-api-call=sorter",
          {
            function: "updateorder",
            order: JSON.stringify(list.toArray()),
            article: article_id,
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

      // update data-width attribute
      parent.setAttribute(
        "data-width",
        parseInt(parent.getAttribute("data-width")) - rex.slicesteps
      );

      $.post(
        "/index.php?rex-api-call=sorter",
        {
          function: "updatewidth",
          slice: slice_id,
          article: article_id,
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

      parent.style.width = width;
      slice_id = parent.getAttribute("data-slice-id");
      article_id = parent.getAttribute("data-article-id");

      // update data-width attribute
      parent.setAttribute(
        "data-width",
        parseInt(parent.getAttribute("data-width")) + rex.slicesteps
      );

      $.post(
        "/index.php?rex-api-call=sorter",
        {
          function: "updatewidth",
          slice: slice_id,
          article: article_id,
          width: parent.getAttribute("data-width"),
        },
        function (result) {
          console.log(result);
        }
      );
    }
  }
});
