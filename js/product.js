$(document).ready(function () {
  let products = [];

  // Fetch Products
  async function fetchProducts() {
    try {
      console.log("Fetching products...");
      const response = await fetch("../../api/productapi.php");
      if (!response.ok)
        throw new Error(`HTTP error! Status: ${response.status}`);

      const data = await response.json();
      console.log("Fetched data:", data);

      if (!data.success || !Array.isArray(data.products)) {
        throw new Error("Invalid data format: Expected 'products' array");
      }

      products = data.products;
      renderProducts(products);
    } catch (error) {
      console.error("Error fetching products:", error);
    }
  }

  // Render Products
  function renderProducts(productsToRender) {
    $("#productList").empty();

    productsToRender.forEach((product) => {
      let imagePath =
        product.image && product.image.trim()
          ? `../../${product.image.trim()}`
          : `assets/images/default.jpg`;

      const price = parseFloat(product.price).toFixed(2);
      const stock = parseInt(product.stock, 10);

      $("#productList").append(`
                <div class="product-container" data-id="${product.id}">
                    <img src="${imagePath}" alt="${product.name}">
                    <h3>${product.name}</h3>
                    <p><strong>Price:</strong> ₱${price}</p>
                    <p><strong>Stock:</strong> ${stock}</p>
                    <p><strong>Category:</strong> ${product.category}</p>
                    <button class="edit-btn" data-id="${product.id}">Edit</button>
                    <button class="delete-btn" data-id="${product.id}">Delete</button>
                </div>
            `);
    });
  }

  // Filter Products by Category
  $("#categoryFilter").change(function () {
    const selectedCategory = $(this).val();
    renderProducts(
      selectedCategory === "All"
        ? products
        : products.filter((p) => p.category === selectedCategory)
    );
  });

  // Handle Edit Product Modal Show
  $("#productList").on("click", ".edit-btn", function () {
    const productId = $(this).data("id");
    const product = products.find((p) => p.id == productId);
    if (!product) return;

    $("#editProductId").val(product.id);
    $("#editProductName").val(product.name);
    $("#editPrice").val(product.price);
    $("#editStock").val(product.stock);
    $("#editCategory").val(product.category);
    $("#editModal").fadeIn();
  });

  // Close Edit Product Modal
  $("#closeModal").click(() => $("#editModal").fadeOut());

  // Handle Edit Product Form Submit
  $("#editForm").submit(function (event) {
    event.preventDefault();

    const productId = $("#editProductId").val();
    const updatedName = $("#editProductName").val();
    const updatedPrice = parseFloat($("#editPrice").val());
    const updatedStock = parseInt($("#editStock").val());
    const updatedCategory = $("#editCategory").val();

    $.ajax({
      url: "../../api/productapi.php",
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify({
        action: "update",
        id: productId,
        name: updatedName,
        price: updatedPrice,
        stock: updatedStock,
        category: updatedCategory,
      }),
      success: function () {
        alert("Product updated successfully!");
        $("#editModal").fadeOut();
        fetchProducts();
      },
      error: function (xhr) {
        console.error("Error updating product:", xhr.responseText);
        alert("Failed to update product.");
      },
    });
  });

  // Handle Delete Product
  $("#productList").on("click", ".delete-btn", function () {
    const productId = $(this).data("id");

    if (!confirm("Are you sure you want to delete this product?")) return;

    $.ajax({
      url: `../../api/productapi.php?id=${productId}`,
      method: "DELETE",
      success: function (response) {
        console.log("Delete response:", response);
        if (response.success) {
          alert("Product deleted successfully!");
          fetchProducts();
        } else {
          alert("Error: " + (response.error || "Failed to delete product."));
        }
      },
      error: function (xhr) {
        console.error("Error deleting product:", xhr.responseText);
        alert("Failed to delete product. Please try again.");
      },
    });
  });

  // Handle Add Product Form Submission
  $("#productForm").submit(function (event) {
    event.preventDefault();

    let formData = new FormData(this);

    $.ajax({
      url: "../../api/add_products.php",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        console.log("Response:", response);

        if (response.success) {
          alert("Product added successfully!");
          $("#addProductModal").fadeOut();
          fetchProducts();
          $("#productForm")[0].reset();
        } else {
          alert("Error: " + (response.error || "Failed to add product."));
        }
      },
      error: function (xhr) {
        console.error("Error adding product:", xhr.responseText);
        alert("Failed to add product. Please try again.");
      },
    });
  });

  // Show Add Product Modal
  $("#addProductBtn").click(function () {
    $("#addProductModal").fadeIn();
  });

  // Close Add Product Modal
  $("#closeAddProductModal").click(function () {
    $("#addProductModal").fadeOut();
  });

  // Initial fetch of products
  fetchProducts();
});
