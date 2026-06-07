@php
  use App\Enums\MenuCategory;
  use App\Enums\PaymentMethod;
@endphp

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="#1a0a00">
  <title>Meja {{ $table->number }} — {{ config('app.name') }}</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
  <link rel="stylesheet" href="{{ asset('static/selforder/show.css') }}">
</head>

<body>
  <div class="toast-wrap" id="toastWrap"></div>
  <header class="top-header">
    <div class="header-brand">
      <div class="header-logo"><i class="fas fa-coffee"></i></div>
      <div class="header-text">
        <h1>{{ config('app.name') }}</h1>
        <small>Self-Ordering Digital</small>
      </div>
    </div>
    <div class="table-badge">
      <i class="fas fa-chair" style="font-size:.75rem; margin-right:4px;"></i>
      Meja {{ $table->number }}
    </div>
  </header>

  <div class="category-tabs-wrap">
    <div class="category-tabs">
      <button class="cat-tab active" data-filter="all">🍽️ Semua</button>
      @foreach ($categories as $category => $menus)
        @php $cat = MenuCategory::from($category); @endphp
        <button class="cat-tab" data-filter="{{ $cat }}">{{ $cat->combined() }}</button>
      @endforeach
    </div>
  </div>

  <main class="main-content">
    <div class="search-wrap">
      <i class="fas fa-search search-icon"></i>
      <input type="text" id="searchInput" placeholder="Cari menu favorit kamu...">
    </div>

    @foreach ($categories as $category => $menus)
      @php $cat = MenuCategory::from($category); @endphp

      <div class="section-label" data-section="{{ $cat }}">{{ $cat->combined() }}</div>

      <div class="menu-list" id="list-{{ $cat }}">
        @foreach ($menus as $menu)
          <div class="menu-item {{ !$menu->available ? 'out' : '' }}" id="menu-item-{{ $menu->id }}"
            data-id="{{ $menu->id }}" data-name="{{ $menu->name }}" data-price="{{ $menu->price }}"
            data-category="{{ $cat }}" data-stock="{{ $menu->stock }}">

            <div class="menu-thumb">
              @if ($menu->image)
                <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}">
              @else
                <span>{{ $cat->emoji() }}</span>
              @endif
              @if (!$menu->available)
                <div class="badge-stock-out">HABIS</div>
              @endif
            </div>

            <div class="menu-info">
              <div class="menu-name">{{ $menu->name }}</div>
              <div class="menu-desc">{{ $menu->description ?? '' }}</div>
              <div class="menu-category">{{ $menu->category->label() }}</div>
              <div class="menu-price">Rp {{ number_format($menu->price, 0, ',', '.') }}</div>
              <div id="stock-{{ $menu->id }}"
                class="menu-stock {{ $menu->stock <= 0 ? 'stock-out' : ($menu->stock <= 5 ? 'stock-low' : 'stock-ok') }}">
                stok: {{ $menu->stock }}
              </div>
            </div>

            @if ($menu->available)
              <div class="qty-control" id="qty-{{ $menu->id }}">
                <button class="qty-btn minus" id="minus-{{ $menu->id }}"
                  onclick="cartDecrement('{{ $menu->id }}')">−</button>
                <span class="qty-num" id="qty-num-{{ $menu->id }}">0</span>
                <button class="qty-btn plus" id="plus-{{ $menu->id }}"
                  onclick="cartIncrement('{{ $menu->id }}')">+</button>
              </div>
            @endif
          </div>
        @endforeach
      </div>
    @endforeach

    @if ($menus->isEmpty())
      <div class="empty-state">
        <i class="fas fa-mug-hot"></i>
        <p>Menu sedang kosong.<br>Mohon kembali sebentar lagi.</p>
      </div>
    @endif

    <div style="height: 20px;"></div>
  </main>

  <div class="cart-bar">
    <div class="cart-bar-inner" id="cartBar">
      <div class="cart-bar-top">
        <div class="cart-bar-info">
          <div class="cart-icon-wrap">
            <i class="fas fa-shopping-bag"></i>
            <span class="cart-dot" id="cartDot">0</span>
          </div>
          <div>
            <strong id="cartItemsText">0 item</strong>
            <span class="cart-bar-text">dalam pesanan kamu</span>
          </div>
        </div>
        <div class="cart-total-price" id="cartTotalBar">Rp 0</div>
      </div>
      <button class="btn-order" id="btnOrder" onclick="openCheckoutModal()">
        <i class="fas fa-clipboard-check" style="margin-right:8px;"></i>
        Pesan Sekarang
      </button>
    </div>
  </div>

  <div class="modal-overlay" id="checkoutModal" onclick="closeModalIfBg(event)">
    <div class="modal-sheet" id="modalSheet">
      <div class="modal-handle"></div>
      <div class="modal-title">Konfirmasi Pesanan</div>
      <div class="modal-subtitle">Meja {{ $table->number }} · Periksa kembali pesanan kamu</div>
      <div class="order-summary-items" id="modalSummaryItems"></div>
      <div class="summary-total">
        <span>Total</span>
        <span id="modalTotal">Rp 0</span>
      </div>
      <div style="height:1px; background:var(--border); margin:16px 0;"></div>
      <div class="form-field">
        <label for="namaPelanggan"><i class="fas fa-user" style="margin-right:5px;"></i>Nama Kamu</label>
        <input type="text" id="namaPelanggan" placeholder="Misal: Budi, Meja kiri..." maxlength="100">
      </div>
      <div style="font-size:.8rem; font-weight:700; color:var(--coffee-brown); margin-bottom:10px;">
        <i class="fas fa-credit-card" style="margin-right:5px;"></i>Metode Pembayaran
      </div>
      <div class="payment-options">
        @foreach (PaymentMethod::values() as $method)
          <label class="payment-option" id="opt-{{ $method }}"
            onclick="selectPayment('{{ $method }}')">
            <input type="radio" name="payment" value="{{ $method }}">
            <div class="payment-icon {{ $method }}">{{ $method->emoji() }}</div>
            <div>
              <div class="payment-label">{{ $method->label() }}</div>
              <div class="payment-desc">{{ $method->desc() }}</div>
            </div>
            <div class="payment-check"><i class="fas fa-check" style="font-size:.7rem;"></i></div>
          </label>
        @endforeach
      </div>
      <button class="btn-confirm" id="btnConfirm" onclick="submitOrder()">
        <i class="fas fa-check-circle" style="margin-right:8px;"></i>
        Konfirmasi Pesanan
      </button>
    </div>
  </div>

  <div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <div class="loading-text">Memproses pesanan...</div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}">
  </script>

  <script>
    const TABLE_ID = '{{ $table->id }}';
    const CHECKOUT_URL = '{{ route('selforder.checkout') }}';
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

    let selectedPayment = 'cash';
    let cart = {};
    let stockMap = {};

    document.querySelectorAll('.menu-item').forEach(el => {
      stockMap[el.dataset.id] = parseInt(el.dataset.stock);
    });

    function cartIncrement(menuId) {
      const key = String(menuId);
      const el = document.getElementById('menu-item-' + menuId);
      if (!el) return;

      const currentQty = cart[key] ? cart[key].qty : 0;
      if (currentQty >= stockMap[key]) {
        Swal.fire('Stok Habis', `Stok "${el.dataset.name}" tidak mencukupi.`, 'warning');
        return;
      }

      if (!cart[key]) {
        cart[key] = {
          id: el.dataset.id,
          name: el.dataset.name,
          price: parseInt(el.dataset.price),
          qty: 1
        };
        showToast('✔ ' + el.dataset.name + ' ditambahkan', 'success');
      } else {
        cart[key].qty++;
      }

      refreshItem(key);
      updateCartBar();

      const newStock = stockMap[key] - 1;
      stockMap[key] = newStock;
      syncStock(menuId, newStock);
    }

    function cartDecrement(menuId) {
      const key = String(menuId);
      if (!cart[key]) return;

      const oldCartQty = cart[key].qty;
      cart[key].qty--;
      if (cart[key].qty <= 0) {
        const name = cart[key].name;
        delete cart[key];
        showToast('🗑 ' + name + ' dihapus', '');
      }

      refreshItem(key);
      updateCartBar();

      stockMap[key] = stockMap[key] + oldCartQty - (cart[key] ? cart[key].qty : 0);
      syncStock(menuId, stockMap[key]);
    }

    function syncStock(menuId, qty) {
      fetch(`/selforder/menu/${menuId}/stock`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json',
        },
        body: JSON.stringify({ qty: qty }),
      }).then(res => res.json()).then(data => {
        if (data.success) {
          const stockEl = document.getElementById('stock-' + menuId);
          if (stockEl) {
            stockEl.textContent = 'stok: ' + data.stock;
            stockEl.className = 'menu-stock ' + (data.stock <= 0 ? 'stock-out' : (data.stock <= 5 ? 'stock-low' : 'stock-ok'));
          }
        }
      }).catch(err => console.error('Stock sync failed:', err));
    }

    function refreshItem(menuId) {
      const key = String(menuId);
      const qty = cart[key] ? cart[key].qty : 0;
      const itemEl = document.getElementById('menu-item-' + menuId);
      const qtyNumEl = document.getElementById('qty-num-' + menuId);
      const minusBtn = document.getElementById('minus-' + menuId);
      const plusBtn = document.getElementById('plus-' + menuId);

      if (!itemEl || !qtyNumEl) return;

      qtyNumEl.textContent = qty;
      itemEl.classList.toggle('in-cart', qty > 0);

      if (minusBtn) {
        minusBtn.style.opacity = qty > 0 ? '1' : '0.2';
        minusBtn.style.pointerEvents = qty > 0 ? 'auto' : 'none';
      }

      if (plusBtn) {
        const atLimit = qty >= stockMap[key];
        plusBtn.style.opacity = atLimit ? '0.2' : '1';
        plusBtn.style.pointerEvents = atLimit ? 'none' : 'auto';
      }
    }

    function updateCartBar() {
      const items = Object.values(cart);
      const totalQty = items.reduce((s, i) => s + i.qty, 0);
      const totalPrice = items.reduce((s, i) => s + i.price * i.qty, 0);

      document.getElementById('cartDot').textContent = totalQty;
      document.getElementById('cartItemsText').textContent = items.length + ' produk · ' + totalQty + ' pcs';
      document.getElementById('cartTotalBar').textContent = formatRp(totalPrice);
      document.getElementById('cartBar').classList.toggle('visible', totalQty > 0);
    }

    function openCheckoutModal() {
      const items = Object.values(cart);
      if (items.length === 0) return;

      let html = '';
      let total = 0;
      items.forEach(item => {
        const sub = item.price * item.qty;
        total += sub;
        html += `
          <div class="summary-item">
            <div>
              <div class="summary-item-name">${item.name}</div>
              <div class="summary-item-qty">${item.qty}x · ${formatRp(item.price)}</div>
            </div>
            <div class="summary-item-price">${formatRp(sub)}</div>
          </div>`;
      });

      document.getElementById('modalSummaryItems').innerHTML = html;
      document.getElementById('modalTotal').textContent = formatRp(total);
      document.getElementById('checkoutModal').classList.add('show');
      setTimeout(() => document.getElementById('namaPelanggan').focus(), 400);
    }

    function closeModal() {
      document.getElementById('checkoutModal').classList.remove('show');
    }

    function closeModalIfBg(e) {
      if (e.target === document.getElementById('checkoutModal')) closeModal();
    }

    function selectPayment(method) {
      selectedPayment = method;
      document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
      document.getElementById('opt-' + method).classList.add('selected');
    }

    function submitOrder() {
      const nama = document.getElementById('namaPelanggan').value.trim();
      if (!nama) {
        document.getElementById('namaPelanggan').focus();
        showToast('⚠ Masukkan nama kamu dulu!', 'error');
        return;
      }

      const items = Object.values(cart);
      if (items.length === 0) return;

      closeModal();
      showLoading(true);

      const payload = {
        table_id: TABLE_ID,
        customer: nama,
        method: selectedPayment,
        items: items.map(i => ({
          id: i.id,
          qty: i.qty
        })),
      };

      fetch(CHECKOUT_URL, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
          },
          body: JSON.stringify(payload),
        })
        .then(r => r.json())
        .then(data => {
          showLoading(false);
          if (!data.success) {
            showToast('❌ ' + (data.message || 'Terjadi kesalahan.'), 'error');
            return;
          }
          if (selectedPayment === 'midtrans') {
            if (!data.snap_token || !data.order_id) {
              showToast('❌ Gagal memproses pembayaran. Silakan pilih metode lain.', 'error');
              return;
            }
            snap.pay(data.snap_token, {
              onSuccess: function(result) {
                showLoading(true);
                fetch('/midtrans/confirm/' + data.order_id, {
                    method: 'POST',
                    headers: {
                      'Accept': 'application/json',
                      'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                  })
                  .then(r => r.json())
                  .finally(() => {
                    showLoading(false);
                    window.location.href = '/selforder/success/' + data.invoice;
                  });
              },
              onPending: function(result) {
                window.location.href = '/selforder/success/' + data.invoice;
              },
              onError: function(result) {
                showLoading(false);
                showToast('❌ Pembayaran gagal.', 'error');
              },
              onClose: function() {
                showLoading(false);
                showToast('⚠ Kamu menutup halaman pembayaran.', 'error');
              }
            });
          } else {
            window.location.href = '/selforder/success/' + data.invoice;
          }
        })
        .catch(err => {
          showLoading(false);
          console.error(err);
          showToast('❌ Koneksi bermasalah, coba lagi.', 'error');
        });
    }

    function showLoading(show) {
      document.getElementById('loadingOverlay').classList.toggle('show', show);
    }

    function formatRp(num) {
      return 'Rp ' + num.toLocaleString('id-ID');
    }

    function showToast(msg, type = '') {
      const wrap = document.getElementById('toastWrap');
      const div = document.createElement('div');
      div.className = 'toast ' + type;
      div.textContent = msg;
      wrap.appendChild(div);
      setTimeout(() => div.remove(), 3000);
    }

    document.querySelectorAll('.cat-tab').forEach(btn => {
      btn.addEventListener('click', function() {
        document.querySelectorAll('.cat-tab').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        document.querySelectorAll('.menu-item').forEach(item => {
          item.style.display = (filter === 'all' || item.dataset.category === filter) ? '' : 'none';
        });
        document.querySelectorAll('[data-section]').forEach(sec => {
          sec.style.display = (filter === 'all' || sec.dataset.section === filter) ? '' : 'none';
        });
      });
    });

    document.getElementById('searchInput').addEventListener('input', function() {
      const q = this.value.toLowerCase().trim();
      document.querySelectorAll('.menu-item').forEach(item => {
        item.style.display = item.dataset.name.toLowerCase().includes(q) ? '' : 'none';
      });
    });

    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('.menu-item:not(.out)').forEach(el => refreshItem(el.dataset.id));
    });
  </script>
</body>
</body>

</html>
