<?php
require_once '../includes/auth_check.php';
checkAuth('admin');
require_once '../config/db.php';
$page_title = 'Manage Products ';
 
$msg = '';
$msg_type = 'success';
 
// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
 
    if ($action === 'add' || $action === 'edit') {
        $name        = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price       = (float)($_POST['price'] ?? 0);
        $stock       = (int)($_POST['stock'] ?? 0);
        $category_id = (int)($_POST['category_id'] ?? 0);
        $image       = trim($_POST['image'] ?? '');
 
        if (empty($name) || $price <= 0) {
            $msg = 'Name and valid price are required.';
            $msg_type = 'error';
        } else {
            if ($action === 'add') {
                $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, category_id, image) VALUES (?,?,?,?,?,?)");
                $stmt->bind_param("ssdiis", $name, $description, $price, $stock, $category_id, $image);
                $stmt->execute();
                $msg = 'Product added successfully.';
            } else {
                $id   = (int)$_POST['product_id'];
                $stmt = $conn->prepare("UPDATE products SET name=?,description=?,price=?,stock=?,category_id=?,image=? WHERE id=?");
                $stmt->bind_param("ssdiisi", $name, $description, $price, $stock, $category_id, $image, $id);
                $stmt->execute();
                $msg = 'Product updated successfully.';
            }
        }
    } elseif ($action === 'delete') {
        $id   = (int)$_POST['product_id'];
        $stmt = $conn->prepare("DELETE FROM products WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $msg = 'Product deleted.';
    }
}
 
// Fetch all products
$products = $conn->query("
    SELECT p.*, c.name as category_name
    FROM products p LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.id DESC
");
$categories = $conn->query("SELECT * FROM categories ORDER BY name");
$cats_arr = [];
while ($c = $categories->fetch_assoc()) $cats_arr[] = $c;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $page_title ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <style>
    :root{--blue-50:#EBF5FF;--blue-100:#C7E4FF;--blue-200:#93CBFF;--blue-400:#3B9EF5;--blue-600:#1A6FBA;--blue-800:#0D3E6E;}
    body{font-family:'DM Sans',sans-serif;background:#F7FBFF;color:#1a2235;display:flex;min-height:100vh;}
    .serif{font-family:'DM Serif Display',serif;}
    .sidebar{width:220px;background:#0D3E6E;min-height:100vh;padding:0;flex-shrink:0;position:fixed;top:0;left:0;height:100%;}
    .sidebar-logo{padding:24px 20px;border-bottom:1px solid rgba(255,255,255,0.1);}
    .nav-item{display:flex;align-items:center;gap:10px;padding:12px 20px;color:#C7E4FF;font-size:14px;font-weight:500;text-decoration:none;transition:background 0.15s;}
    .nav-item:hover,.nav-item.active{background:rgba(59,158,245,0.2);color:#fff;}
    .nav-section{padding:8px 20px;font-size:10px;color:rgba(255,255,255,0.3);font-weight:600;letter-spacing:0.08em;text-transform:uppercase;margin-top:8px;}
    .main-content{margin-left:220px;flex:1;padding:32px;}
    .form-input{width:100%;padding:10px 12px;border:1.5px solid #d9eeff;border-radius:10px;font-size:14px;font-family:'DM Sans',sans-serif;outline:none;transition:border-color 0.2s;background:#F7FBFF;}
    .form-input:focus{border-color:#3B9EF5;box-shadow:0 0 0 3px rgba(59,158,245,0.1);background:#fff;}
    .form-label{display:block;font-size:13px;font-weight:500;color:#475569;margin-bottom:5px;}
    table{width:100%;border-collapse:collapse;font-size:14px;}
    thead{background:#F7FBFF;}
    th{padding:12px 16px;text-align:left;color:#64748b;font-weight:500;font-size:13px;}
    td{padding:13px 16px;border-top:1px solid #f0f8ff;}
    /* Modal */
    .modal-overlay{position:fixed;inset:0;background:rgba(13,62,110,0.4);z-index:100;display:none;align-items:center;justify-content:center;}
    .modal-overlay.open{display:flex;}
    .modal{background:#fff;border-radius:20px;padding:32px;width:100%;max-width:480px;max-height:90vh;overflow-y:auto;}
  </style>
</head>
<body>
 
<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <a href="../index.html" style="display:flex;align-items:center;gap:8px;text-decoration:none;">
      <div style="width:30px;height:30px;background:#3B9EF5;border-radius:8px;display:flex;align-items:center;justify-content:center;">
        <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M4 4h16v2H4V4zm0 4h10v2H4V8zm0 4h16v2H4v-2zm0 4h10v2H4v-2z" fill="#fff"/></svg>
      </div>
      <span class="serif text-xl text-white">Paperly</span>
    </a>
  </div>
  <nav style="padding:16px 0;">
    <div class="nav-section">Main</div>
    <a href="dashboard.php" class="nav-item">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg> Dashboard
    </a>
    <div class="nav-section">Manage</div>
    <a href="products.php" class="nav-item active">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg> Products
    </a>
    <a href="orders.php" class="nav-item">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg> Orders
    </a>
    <a href="users.php" class="nav-item">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg> Users
    </a>
    <div class="nav-section">Account</div>
    <a href="../logout.php" class="nav-item" style="color:#f87171;">
      <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> Logout
    </a>
  </nav>
</aside>
 
<!-- MAIN -->
<main class="main-content">
  <div class="flex items-center justify-between mb-6">
    <div>
      <h1 class="serif text-3xl" style="color:#0D3E6E;">Products</h1>
      <p style="color:#94a3b8;font-size:14px;margin-top:2px;"><?= $products->num_rows ?> products total</p>
    </div>
    <button onclick="openModal('add')"
            style="background:#3B9EF5;color:#fff;border:none;border-radius:50px;padding:10px 22px;font-weight:600;font-size:14px;cursor:pointer;font-family:'DM Sans',sans-serif;">
      + Add Product
    </button>
  </div>
 
  <?php if ($msg): ?>
  <div style="background:<?= $msg_type==='error'?'#fff0f0':'#f0fdf4' ?>;border:1px solid <?= $msg_type==='error'?'#fca5a5':'#86efac' ?>;color:<?= $msg_type==='error'?'#dc2626':'#16a34a' ?>;border-radius:10px;padding:10px 16px;margin-bottom:16px;font-size:14px;">
    <?= htmlspecialchars($msg) ?>
  </div>
  <?php endif; ?>
 
  <!-- Products Table -->
  <div style="background:#fff;border:1px solid #d9eeff;border-radius:18px;overflow:hidden;">
    <table>
      <thead>
        <tr>
          <th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($p = $products->fetch_assoc()): ?>
        <tr>
          <td>
            <div style="font-weight:600;color:#0D3E6E;"><?= htmlspecialchars($p['name']) ?></div>
            <div style="font-size:12px;color:#94a3b8;margin-top:2px;"><?= htmlspecialchars(substr($p['description'] ?? '', 0, 50)) ?></div>
          </td>
          <td style="color:#64748b;"><?= htmlspecialchars($p['category_name'] ?? '—') ?></td>
          <td style="font-weight:600;color:#1A6FBA;">Rs. <?= number_format($p['price'], 2) ?></td>
          <td>
            <span style="background:<?= $p['stock']>10?'#dcfce7':($p['stock']>0?'#fef9c3':'#fee2e2') ?>;color:<?= $p['stock']>10?'#15803d':($p['stock']>0?'#854d0e':'#dc2626') ?>;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:600;">
              <?= $p['stock'] ?>
            </span>
          </td>
          <td>
            <button onclick='editProduct(<?= json_encode($p) ?>)'
                    style="background:#EBF5FF;color:#1A6FBA;border:1.5px solid #93CBFF;border-radius:8px;padding:6px 14px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif;margin-right:6px;">Edit</button>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this product?')">
              <input type="hidden" name="action" value="delete"/>
              <input type="hidden" name="product_id" value="<?= $p['id'] ?>"/>
              <button type="submit" style="background:#fff0f0;color:#dc2626;border:1.5px solid #fca5a5;border-radius:8px;padding:6px 14px;font-size:13px;cursor:pointer;font-family:'DM Sans',sans-serif;">Delete</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</main>
 
<!-- ADD/EDIT MODAL -->
<div class="modal-overlay" id="productModal">
  <div class="modal">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
      <h2 class="serif text-2xl" style="color:#0D3E6E;" id="modalTitle">Add Product</h2>
      <button onclick="closeModal()" style="background:none;border:none;cursor:pointer;color:#94a3b8;font-size:22px;line-height:1;">×</button>
    </div>
    <form method="POST" id="productForm">
      <input type="hidden" name="action" id="formAction" value="add"/>
      <input type="hidden" name="product_id" id="formProductId"/>
 
      <div style="margin-bottom:14px;">
        <label class="form-label">Product Name *</label>
        <input type="text" name="name" id="fName" class="form-input" placeholder="e.g. Classic Ruled Notebook" required/>
      </div>
      <div style="margin-bottom:14px;">
        <label class="form-label">Description</label>
        <textarea name="description" id="fDesc" class="form-input" rows="2" placeholder="Short product description" style="resize:vertical;"></textarea>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
        <div>
          <label class="form-label">Price (Rs.) *</label>
          <input type="number" name="price" id="fPrice" class="form-input" placeholder="0.00" step="0.01" min="0" required/>
        </div>
        <div>
          <label class="form-label">Stock</label>
          <input type="number" name="stock" id="fStock" class="form-input" placeholder="0" min="0"/>
        </div>
      </div>
      <div style="margin-bottom:14px;">
        <label class="form-label">Category</label>
        <select name="category_id" id="fCat" class="form-input">
          <option value="">— Select category —</option>
          <?php foreach($cats_arr as $c): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div style="margin-bottom:20px;">
        <label class="form-label">Image filename (e.g. notebook.jpg)</label>
        <input type="text" name="image" id="fImage" class="form-input" placeholder="product.jpg"/>
      </div>
      <div style="display:flex;gap:10px;">
        <button type="submit" style="flex:1;background:#3B9EF5;color:#fff;border:none;border-radius:10px;padding:12px;font-weight:600;font-size:14px;cursor:pointer;font-family:'DM Sans',sans-serif;">Save Product</button>
        <button type="button" onclick="closeModal()" style="flex:1;background:#F7FBFF;color:#64748b;border:1.5px solid #d9eeff;border-radius:10px;padding:12px;font-weight:600;font-size:14px;cursor:pointer;font-family:'DM Sans',sans-serif;">Cancel</button>
      </div>
    </form>
  </div>
</div>
 
<script>
function openModal(mode) {
  document.getElementById('productModal').classList.add('open');
  document.getElementById('modalTitle').textContent = mode === 'add' ? 'Add Product' : 'Edit Product';
  document.getElementById('formAction').value = mode;
  if (mode === 'add') {
    document.getElementById('productForm').reset();
    document.getElementById('formProductId').value = '';
  }
}
function closeModal() {
  document.getElementById('productModal').classList.remove('open');
}
function editProduct(p) {
  openModal('edit');
  document.getElementById('formProductId').value = p.id;
  document.getElementById('fName').value          = p.name;
  document.getElementById('fDesc').value          = p.description || '';
  document.getElementById('fPrice').value         = p.price;
  document.getElementById('fStock').value         = p.stock;
  document.getElementById('fCat').value           = p.category_id || '';
  document.getElementById('fImage').value         = p.image || '';
}
// Close on backdrop click
document.getElementById('productModal').addEventListener('click', function(e) {
  if (e.target === this) closeModal();
});
</script>
</body>
</html>
 