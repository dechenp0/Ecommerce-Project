<?php
require_once '../includes/auth_check.php';
checkAuth('user');
$page_title = 'Shop Products – Paperly';
include '../includes/header.php';
?>
<div class="max-w-6xl mx-auto px-6 py-10">
 
  <!-- Page title + search -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
    <div>
      <h1 class="serif text-3xl" style="color:#0D3E6E;">All Products</h1>
      <p class="text-gray-400 text-sm mt-1">Browse our full collection of stationery</p>
    </div>
    <div style="position:relative;">
      <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#93CBFF;" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" id="searchInput" placeholder="Search products..."
             style="padding:10px 14px 10px 38px;border:1.5px solid #d9eeff;border-radius:50px;font-size:14px;font-family:'DM Sans',sans-serif;outline:none;width:260px;background:#fff;transition:border-color 0.2s;"
             oninput="loadProducts()"
             onfocus="this.style.borderColor='#3B9EF5'" onblur="this.style.borderColor='#d9eeff'"/>
    </div>
  </div>
 
  <!-- Category chips -->
  <div class="flex flex-wrap gap-2 mb-8" id="categoryChips">
    <button class="cat-chip active" data-cat="0" onclick="selectCat(this,0)">All</button>
    <!-- Loaded from DB via PHP -->
    <?php
      require_once '../config/db.php';
      $cats = $conn->query("SELECT * FROM categories ORDER BY name");
      while($cat = $cats->fetch_assoc()):
    ?>
    <button class="cat-chip" data-cat="<?= $cat['id'] ?>" onclick="selectCat(this,<?= $cat['id'] ?>)"><?= htmlspecialchars($cat['name']) ?></button>
    <?php endwhile; ?>
  </div>
 
  <!-- Product Grid -->
  <div id="productGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
    <div class="col-span-4 text-center py-12 text-gray-300">Loading products...</div>
  </div>
 
  <!-- Toast -->
  <div id="toast" style="position:fixed;bottom:28px;right:28px;z-index:999;background:#0D3E6E;color:#fff;padding:12px 22px;border-radius:12px;font-size:14px;font-weight:500;transform:translateY(80px);opacity:0;transition:transform 0.3s,opacity 0.3s;pointer-events:none;">
    ✓ Added to cart!
  </div>
</div>
 
<style>
  .cat-chip {
    border:1.5px solid #d9eeff;color:#1A6FBA;border-radius:50px;
    padding:7px 18px;font-size:13px;font-weight:500;cursor:pointer;
    transition:background 0.18s,color 0.18s;background:#fff;
    font-family:'DM Sans',sans-serif;
  }
  .cat-chip.active,.cat-chip:hover { background:#3B9EF5;color:#fff;border-color:#3B9EF5; }
  .product-card {
    background:#fff;border-radius:18px;border:1px solid #d9eeff;
    overflow:hidden;transition:transform 0.22s,box-shadow 0.22s;
  }
  .product-card:hover { transform:translateY(-5px);box-shadow:0 16px 40px rgba(59,158,245,0.13); }
  .add-btn {
    width:100%;background:#EBF5FF;color:#1A6FBA;border:1.5px solid #93CBFF;
    border-radius:50px;padding:8px 18px;font-weight:600;font-size:13px;
    transition:background 0.2s,color 0.2s;cursor:pointer;font-family:'DM Sans',sans-serif;
  }
  .add-btn:hover { background:#3B9EF5;color:#fff;border-color:#3B9EF5; }
  .add-btn:disabled { opacity:0.4;cursor:not-allowed; }
  @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
</style>
 
<script>
const emojis = { 1:'📓', 2:'✒️', 3:'🎨', 4:'📎', 5:'📅' };
let currentCat = 0;
 
function selectCat(el, cat) {
  document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
  el.classList.add('active');
  currentCat = cat;
  loadProducts();
}
 
async function loadProducts() {
  const search = document.getElementById('searchInput').value;
  const params = new URLSearchParams();
  if (currentCat > 0) params.set('category', currentCat);
  if (search) params.set('search', search);
 
  const res  = await fetch(`../api/get_products.php?${params}`);
  const data = await res.json();
  renderProducts(data.products);
}
 
function renderProducts(products) {
  const grid = document.getElementById('productGrid');
  if (!products.length) {
    grid.innerHTML = '<div class="col-span-4 text-center py-16 text-gray-300"><p style="font-size:36px;margin-bottom:8px;">🔍</p><p>No products found</p></div>';
    return;
  }
  grid.innerHTML = products.map((p, i) => `
    <div class="product-card" style="animation:fadeUp 0.4s ${i*0.06}s ease both;">
      <div style="background:#EBF5FF;height:150px;display:flex;align-items:center;justify-content:center;font-size:48px;position:relative;">
        ${p.image_url
          ? `<img src="${p.image_url}" alt="${p.name}" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;">`
          : (emojis[p.category_id] || '📦')
        }
      </div>
      <div style="padding:14px;">
        <p style="font-size:12px;color:#64748b;margin-bottom:2px;">${p.category_name}</p>
        <h3 style="font-weight:600;font-size:14px;color:#0D3E6E;margin-bottom:6px;line-height:1.3;">${p.name}</h3>
        ${p.description ? `<p style="font-size:12px;color:#94a3b8;margin-bottom:8px;line-height:1.5;">${p.description.substring(0,60)}${p.description.length>60?'...':''}</p>` : ''}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
          <span style="font-weight:700;font-size:16px;color:#1A6FBA;">Rs. ${p.price}</span>
          <span style="font-size:11px;color:${p.stock>0?'#16a34a':'#dc2626'};">${p.stock>0?`${p.stock} left`:'Out of stock'}</span>
        </div>
        <button class="add-btn" onclick="addToCart(${p.id},'${p.name}')" ${p.stock==0?'disabled':''}>
          ${p.stock==0 ? 'Out of Stock' : '+ Add to Cart'}
        </button>
      </div>
    </div>
  `).join('');
}
 
async function addToCart(product_id, name) {
  const res  = await fetch('../api/add_to_cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ product_id, quantity: 1 })
  });
  const data = await res.json();
 
  if (data.redirect) { window.location.href = data.redirect; return; }
  if (data.success) {
    // Update cart count in navbar
    document.querySelector('.cart-count').textContent = data.cart_count;
    showToast(`✓ ${name} added to cart!`);
  } else {
    showToast('⚠ ' + data.message, true);
  }
}
 
function showToast(msg, isError = false) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.background = isError ? '#dc2626' : '#0D3E6E';
  t.style.transform = 'translateY(0)';
  t.style.opacity   = '1';
  setTimeout(() => { t.style.transform='translateY(80px)'; t.style.opacity='0'; }, 2500);
}
 
// Initial load
loadProducts();
</script>
<?php include '../includes/footer.php'; ?>