$(document).on("rex:ready", function () {
  var mygrid = GridStack.init();
  // Note: the HTMLElement (of type GridHTMLElement) will store a `gridstack: GridStack` value that can be retrieve later
  //   var mygrid = document.getElementsByClassName("rex-slices")[0].gridstack;

  var mygrid = GridStack.init(
    {
      'alwaysShowResizeHandle': true,
      'min-height': 800,
    },
    ".rex-slices"
  );
  //   mygrid.addWidget({ w: 2, content: "item 1" });
  //   mygrid.addWidget({ w: 2, content: "item 2" });
  //   mygrid.addWidget({ w: 2, content: "item 3" });
  //   mygrid.addWidget({ w: 2, content: "item 4" });
  //   mygrid.addWidget({ w: 2, content: "item 5" });

  //   grid.addWidget({ w: 2, content: "item 1" });
});
