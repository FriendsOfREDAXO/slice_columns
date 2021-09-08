$(document).on("rex:ready", function () {
  const number_columns = 6;
  var store = {};

  //   console.log("initialising columns...");

  var el = document.getElementsByClassName("rex-slices")[0];

  var list = Sortable.create(el, {
    onChange: function (evt) {
      // console.log("old: " + evt.oldDraggableIndex);
      // console.log("new: " + evt.newDraggableIndex);
      // console.log("Order:");
      // console.log(list.toArray());
      // console.log("Widths:");
      // console.log(store);

      $.post(
        "/index.php?rex-api-call=sorter",
        { function: "updateorder", order: JSON.stringify(list.toArray()) },
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

  function smaller(el) {
    target = el.target;
    // let parent = target.closest(".rex-slice");
    let parent = target.closest(".dragdrop");

    let attr_width = parseInt(parent.getAttribute('data-width'))
    width = 100 * ((attr_width - 1) / 6) + "%";
    
    parent.style.width = width;
    slice_id = parent.getAttribute("data-slice-id");
    article_id = parent.getAttribute("data-article-id");

    // update data-width attribute
    parent.setAttribute(
      "data-width",
      parseInt(parent.getAttribute("data-width")) - 1
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

  function wider(el) {
    target = el.target;
    // let parent = target.closest(".rex-slice");
    let parent = target.closest(".dragdrop");
    
    let attr_width = parseInt(parent.getAttribute('data-width'))
    width = 100 * ((attr_width + 1) / 6) + "%";
    
    parent.style.width = width;
    slice_id = parent.getAttribute("data-slice-id");
    article_id = parent.getAttribute("data-article-id");

    // update data-width attribute
    parent.setAttribute(
      "data-width",
      parseInt(parent.getAttribute("data-width")) + 1
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
});
