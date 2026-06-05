<?php
require_once '../includes/auth_check.php';
checkAuth('user');
require_once '../config/db.php';
$page_title = 'Shop Products – Paperly';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?= $page_title ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap" rel="stylesheet"/>
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
      display:flex;flex-direction:column;
    }
    .product-card:hover { transform:translateY(-5px);box-shadow:0 16px 40px rgba(59,158,245,0.13); }

    .add-btn {
      width:100%;background:#EBF5FF;color:#1A6FBA;border:1.5px solid #93CBFF;
      border-radius:50px;padding:8px 18px;font-weight:600;font-size:13px;
      transition:background 0.2s,color 0.2s;cursor:pointer;font-family:'DM Sans',sans-serif;
    }
    .add-btn:hover { background:#3B9EF5;color:#fff;border-color:#3B9EF5; }
    .add-btn:disabled { opacity:0.4;cursor:not-allowed; }
    .add-btn.adding { background:#3B9EF5;color:#fff;pointer-events:none; }

    @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }

    #toast {
      position:fixed;bottom:28px;right:28px;z-index:9999;
      background:#0D3E6E;color:#fff;padding:12px 22px;
      border-radius:12px;font-size:14px;font-weight:500;
      transform:translateY(80px);opacity:0;
      transition:transform 0.3s,opacity 0.3s;pointer-events:none;
      box-shadow:0 8px 24px rgba(13,62,110,0.25);
    }
  </style>
</head>
<body>

<?php include '../includes/sidebar.php'; ?>

<main class="u-main">

  <!-- Page Header -->
  <div style="margin-bottom:28px;">
    <h1 style="font-family:'DM Serif Display',serif;font-size:28px;color:#0D3E6E;">All Products</h1>
    <p style="color:#94a3b8;font-size:14px;margin-top:4px;">Browse our full collection of stationery</p>
  </div>

  <!-- Search + Filter Row -->
  <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:22px;">

    <!-- Category chips -->
    <div style="display:flex;flex-wrap:wrap;gap:8px;" id="categoryChips">
      <button class="cat-chip active" data-cat="0" onclick="selectCat(this,0)">All</button>
      <?php
        $cats = $conn->query("SELECT * FROM categories ORDER BY name");
        while($cat = $cats->fetch_assoc()):
      ?>
      <button class="cat-chip" data-cat="<?= $cat['id'] ?>" onclick="selectCat(this,<?= $cat['id'] ?>)">
        <?= htmlspecialchars($cat['name']) ?>
      </button>
      <?php endwhile; ?>
    </div>

    <!-- Search -->
    <div style="position:relative;flex-shrink:0;">
      <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#93CBFF;" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <input type="text" id="searchInput" placeholder="Search products..."
             style="padding:9px 14px 9px 36px;border:1.5px solid #d9eeff;border-radius:50px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;width:230px;background:#fff;transition:border-color 0.2s;"
             oninput="loadProducts()"
             onfocus="this.style.borderColor='#3B9EF5'"
             onblur="this.style.borderColor='#d9eeff'"/>
    </div>
  </div>

  <!-- Product Grid -->
  <div id="productGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
    <div class="col-span-4 text-center py-12" style="color:#cbd5e1;">
      <p style="font-size:32px;margin-bottom:8px;">⏳</p>
      <p>Loading products...</p>
    </div>
  </div>

</main>

<!-- Toast -->
<div id="toast"></div>

<script>
const emojis     = { 1:'📓', 2:'✒️', 3:'🎨', 4:'📎', 5:'📅' };
const BASE_IMG   = '../assets/images/products/';   // path from user/ folder
let   currentCat = 0;

// ── Category filter ──────────────────────────────────────────────
function selectCat(el, cat) {
  document.querySelectorAll('.cat-chip').forEach(c => c.classList.remove('active'));
  el.classList.add('active');
  currentCat = cat;
  loadProducts();
}

// ── Fetch products from API ───────────────────────────────────────
async function loadProducts() {
  const search = document.getElementById('searchInput').value.trim();
  const params = new URLSearchParams();
  if (currentCat > 0) params.set('category', currentCat);
  if (search)         params.set('search', search);

  try {
    const res  = await fetch(`../api/get_products.php?${params}`);
    const data = await res.json();
    renderProducts(data.products || []);
  } catch (err) {
    document.getElementById('productGrid').innerHTML =
      '<div class="col-span-4 text-center py-12" style="color:#ef4444;">Failed to load products. Please refresh.</div>';
  }
}

// ── Render product cards ──────────────────────────────────────────
function renderProducts(products) {
  const grid = document.getElementById('productGrid');

  if (!products.length) {
    grid.innerHTML = `
      <div class="col-span-4 text-center py-16" style="color:#cbd5e1;">
        <p style="font-size:36px;margin-bottom:8px;">🔍</p>
        <p>No products found</p>
      </div>`;
    return;
  }

  grid.innerHTML = products.map((p, i) => {

    // Build image: use filename from API, prepend correct relative path
    const imgFilename = p.image_url
      ? p.image_url.split('/').pop()   // grab just filename, strip any old path
      : null;

    const imgHtml = imgFilename
      ? `<img src="${BASE_IMG}${imgFilename}" alt="${p.name}"
              style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;"
              onerror="this.style.display='none'">`
      : `<span style="font-size:48px;">${emojis[p.category_id] || '📦'}</span>`;

    return `
    <div class="product-card" style="animation:fadeUp 0.4s ${i*0.06}s ease both;">

      <!-- Image area -->
      <div style="background:#EBF5FF;height:160px;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;flex-shrink:0;">
        ${imgHtml}
      </div>

      <!-- Details -->
      <div style="padding:14px;display:flex;flex-direction:column;flex:1;">
        <p style="font-size:11px;color:#94a3b8;font-weight:500;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:2px;">
          ${p.category_name || ''}
        </p>
        <h3 style="font-weight:600;font-size:14px;color:#0D3E6E;margin-bottom:6px;line-height:1.35;flex:1;">
          ${p.name}
        </h3>
        ${p.description
          ? `<p style="font-size:12px;color:#94a3b8;margin-bottom:8px;line-height:1.5;">
               ${p.description.substring(0,60)}${p.description.length>60?'...':''}
             </p>`
          : ''}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
          <span style="font-weight:700;font-size:16px;color:#1A6FBA;">Rs. ${Number(p.price).toLocaleString('en-IN')}</span>
          <span style="font-size:11px;font-weight:600;color:${p.stock>0?'#16a34a':'#dc2626'};">
            ${p.stock>0 ? `${p.stock} left` : 'Out of stock'}
          </span>
        </div>
        <button class="add-btn" id="btn-${p.id}"
                onclick="addToCart(${p.id},'${p.name.replace(/'/g,"\\'")}',this)"
                ${p.stock==0 ? 'disabled' : ''}>
          ${p.stock==0 ? 'Out of Stock' : '+ Add to Cart'}
        </button>
      </div>
    </div>`;
  }).join('');
}

// ── Add to cart ───────────────────────────────────────────────────
async function addToCart(product_id, name, btn) {
  btn.classList.add('adding');
  btn.textContent = 'Adding...';

  try {
    const res  = await fetch('../api/add_to_cart.php', {
      method : 'POST',
      headers: { 'Content-Type': 'application/json' },
      body   : JSON.stringify({ product_id, quantity: 1 })
    });
    const data = await res.json();

    if (data.redirect) { window.location.href = data.redirect; return; }

    if (data.success) {
      // Update cart badge in sidebar
      const badge = document.querySelector('.cart-badge');
      if (badge) badge.textContent = data.cart_count;

      btn.textContent = '✓ Added!';
      btn.style.background = '#16a34a';
      btn.style.borderColor = '#16a34a';
      btn.style.color = '#fff';

      setTimeout(() => {
        btn.classList.remove('adding');
        btn.textContent = '+ Add to Cart';
        btn.style.background = '';
        btn.style.borderColor = '';
        btn.style.color = '';
      }, 1800);

      showToast(`✓ ${name} added to cart!`);
    } else {
      btn.classList.remove('adding');
      btn.textContent = '+ Add to Cart';
      showToast('⚠ ' + (data.message || 'Could not add to cart'), true);
    }
  } catch (err) {
    btn.classList.remove('adding');
    btn.textContent = '+ Add to Cart';
    showToast('⚠ Something went wrong', true);
  }
}

// ── Toast ─────────────────────────────────────────────────────────
let toastTimer;
function showToast(msg, isError = false) {
  const t = document.getElementById('toast');
  t.textContent      = msg;
  t.style.background = isError ? '#dc2626' : '#0D3E6E';
  t.style.transform  = 'translateY(0)';
  t.style.opacity    = '1';
  clearTimeout(toastTimer);
  toastTimer = setTimeout(() => {
    t.style.transform = 'translateY(80px)';
    t.style.opacity   = '0';
  }, 2500);
}

// ── Init ──────────────────────────────────────────────────────────
loadProducts();
</script>

</body>
</html>