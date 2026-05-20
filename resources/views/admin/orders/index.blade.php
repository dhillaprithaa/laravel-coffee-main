@php
  use App\Enums\MenuCategory;
  use App\Enums\PaymentMethod;
@endphp

@extends('layouts.admin')
@section('title', 'Kasir / POS')
@section('page-title', 'Kasir / POS')

@push('styles')
  <link rel="stylesheet" href="{{ asset('static/admin/orders/index.css') }}">
@endpush

@section('content')
  <div class="kasir-wrapper">
    <div class="menu-panel">
      <div class="menu-panel-header">
        <h5><i class="fas fa-utensils mr-2"></i>Daftar Menu</h5>
        <span style="font-size:.8rem; opacity:.8;">{{ $menus->count() }} item tersedia</span>
      </div>
      <div class="menu-filter-tabs">
        <button class="filter-tab active" data-filter="all">Semua</button>
        <button class="filter-tab" data-filter="{{ MenuCategory::MINUMAN }}">
          <i class="fas fa-coffee mr-1"></i>Minuman
        </button>
        <button class="filter-tab" data-filter="{{ MenuCategory::MAKANAN }}">
          <i class="fas fa-hamburger mr-1"></i>Makanan
        </button>
      </div>
      <div class="menu-search-wrap position-relative">
        <i class="fas fa-search menu-search-icon"></i>
        <input type="text" id="menuSearch" class="form-control" placeholder="Cari menu...">
      </div>
      <div class="menu-grid-wrap">
        <div class="menu-grid" id="menuGrid">
          @foreach ($menus as $menu)
            <div class="menu-card {{ !$menu->available ? 'out-of-stock' : '' }}" data-id="{{ $menu->id }}"
              data-name="{{ $menu->name }}" data-price="{{ $menu->price }}" data-category="{{ $menu->category }}"
              data-stock="{{ $menu->stock }}" onclick="addToCart(this)">
              <div class="menu-card-img">
                @if ($menu->image)
                  <img src="{{ $menu->image_url }}" alt="{{ $menu->name }}">
                @else
                  {{ $menu->category->emoji() }}
                @endif
              </div>
              <div class="menu-card-body">
                <p class="menu-card-name">{{ $menu->name }}</p>
                <p class="menu-card-desc">{{ $menu->description ?? '' }}</p>
                <p class="menu-card-price">Rp {{ number_format($menu->price, 0, ',', '.') }}</p>
                <p class="menu-card-stock">{{ $menu->stock }}</p>
              </div>
            </div>
          @endforeach
        </div>
        @if ($menus->isEmpty())
          <div class="text-center text-muted py-5">
            <i class="fas fa-box-open fa-3x mb-3"></i>
            <p>Tidak ada menu tersedia</p>
          </div>
        @endif
      </div>
    </div>
    <div class="cart-panel">
      <div class="cart-header">
        <h5><i class="fas fa-shopping-cart mr-2"></i>Keranjang</h5>
        <span class="cart-count" id="cartCount">0 item</span>
      </div>
      <div class="cart-items" id="cartItems">
        <div class="cart-empty" id="cartEmpty">
          <i class="fas fa-shopping-cart"></i>
          <p>Keranjang kosong</p>
          <small>Pilih menu di sebelah kiri</small>
        </div>
      </div>
      <div class="cart-footer">
        <div class="cart-subtotal">
          <span>Subtotal</span>
          <span id="cartSubtotal">Rp 0</span>
        </div>
        <div class="cart-total">
          <span>Total</span>
          <span id="cartTotal">Rp 0</span>
        </div>
        <div style="margin-bottom:12px;">
          <label style="font-size:.78rem; font-weight:700; color:#7b4a1e; margin-bottom:4px; display:block;">
            <i class="fas fa-user mr-1"></i>Nama Pelanggan
          </label>
          <input type="text" id="namaPelanggan" placeholder="Contoh: Budi, Meja 3..."
            style="width:100%; border:2px solid #e0d5c8; border-radius:10px; padding:8px 12px;
                              font-size:.88rem; color:#2d1200; font-family:inherit; outline:none;
                              transition:border-color .2s;"
            onfocus="this.style.borderColor='#7b4a1e'" onblur="this.style.borderColor='#e0d5c8'">
        </div>
        <select class="payment-select" id="paymentMethod">
          @foreach (PaymentMethod::values() as $method)
            <option value="{{ $method }}">{{ $method->combined() }}</option>
          @endforeach
        </select>
        <button class="btn-checkout" id="btnCheckout" disabled onclick="doCheckout()">
          <i class="fas fa-check-circle mr-2"></i>
          Proses Pembayaran
        </button>
        <div class="text-center mt-2">
          <button class="btn-clear" onclick="clearCart()">
            <i class="fas fa-trash mr-1"></i> Kosongkan Keranjang
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="nota-overlay" id="notaOverlay">
    <div class="nota-box" id="printArea">
      <div class="nota-header">
        <h5>☕ {{ config('app.name') }}</h5>
        <small id="notaDate"></small>
      </div>
      <div class="nota-body">
        <div class="nota-row"><span class="label">Invoice</span><span class="value" id="notaInvoice">-</span></div>
        <div class="nota-row" id="notaNamaRow" style="display:none;"><span class="label">Pelanggan</span><span
            class="value" id="notaNama" style="color:#d4a843; font-weight:800;">-</span></div>
        <div class="nota-row"><span class="label">Tipe Order</span><span class="value" id="notaTipe">-</span></div>
        <div class="nota-row"><span class="label">Kasir</span><span class="value">{{ Auth::user()->name }}</span>
        </div>
        <div class="nota-row"><span class="label">Pembayaran</span><span class="value" id="notaMetode">-</span>
        </div>
        <hr class="nota-divider">
        <div class="nota-items-header">Detail Pesanan</div>
        <div id="notaItemsList"></div>
        <hr class="nota-divider">
        <div class="nota-total-row">
          <span>TOTAL</span>
          <span class="total-val" id="notaTotal">-</span>
        </div>
        <hr class="nota-divider">
        <div style="text-align:center; font-size:.78rem; color:#aaa; margin-top:8px;">
          Terima kasih sudah berkunjung!<br>
          <em>Sampai jumpa lagi ☕</em>
        </div>
      </div>
      <div class="nota-footer no-print">
        <button class="btn-print" onclick="printNota()">
          <i class="fas fa-print mr-2"></i>Cetak Nota
        </button>
        <button class="btn-close-nota" onclick="closeNota()">
          Tutup
        </button>
      </div>
    </div>
  </div>

  <div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <p>Memproses pembayaran...</p>
  </div>
@endsection

@push('scripts')
  <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}">
  </script>
  <script>
    let cart = {};
    let stockMap = {};

    document.querySelectorAll('.menu-card').forEach(card => {
      stockMap[card.dataset.id] = parseInt(card.dataset.stock);
    });

    function addToCart(el) {
      const id = el.dataset.id;
      if (el.classList.contains('out-of-stock')) return;
      const currentQtyInCart = cart[id] ? cart[id].qty : 0;
      if (currentQtyInCart >= stockMap[id]) {
        Swal.fire('Stok Habis', `Stok "${el.dataset.name}" tidak mencukupi.`, 'warning');
        return;
      }
      if (cart[id]) {
        cart[id].qty++;
      } else {
        cart[id] = {
          id,
          name: el.dataset.name,
          price: parseInt(el.dataset.price),
          qty: 1
        };
      }
      renderCart();
      showAddedFeedback(el);
    }

    function showAddedFeedback(el) {
      el.style.borderColor = '#4e2a04';
      el.style.transform = 'scale(0.97)';
      setTimeout(() => {
        el.style.borderColor = '';
        el.style.transform = '';
      }, 200);
    }

    function changeQty(id, delta) {
      if (!cart[id]) return;
      const newQty = cart[id].qty + delta;
      if (delta > 0 && newQty > stockMap[id]) {
        Swal.fire('Stok Habis', `Stok "${cart[id].name}" tidak mencukupi.`, 'warning');
        return;
      }
      cart[id].qty = newQty;
      if (cart[id].qty <= 0) delete cart[id];
      renderCart();
    }

    function removeItem(id) {
      delete cart[id];
      renderCart();
    }

    function clearCart() {
      cart = {};
      renderCart();
    }

    function renderCart() {
      const items = Object.values(cart);
      const totalQty = items.reduce((s, i) => s + i.qty, 0);
      const totalprice = items.reduce((s, i) => s + i.price * i.qty, 0);
      document.getElementById('cartCount').textContent = totalQty + ' item';
      document.getElementById('cartSubtotal').textContent = formatRp(totalprice);
      document.getElementById('cartTotal').textContent = formatRp(totalprice);
      document.getElementById('btnCheckout').disabled = items.length === 0;
      const cartItemsEl = document.getElementById('cartItems');
      if (items.length === 0) {
        cartItemsEl.innerHTML = `
            <div class="cart-empty" id="cartEmpty">
                <i class="fas fa-shopping-cart"></i>
                <p>Keranjang kosong</p>
                <small>Pilih menu di sebelah kiri</small>
            </div>`;
        return;
      }
      let html = '';
      items.forEach(item => {
        html += `
            <div class="cart-item" id="cart-item-${item.id}">
                <span class="cart-item-emoji">🍽️</span>
                <div class="cart-item-info">
                    <div class="cart-item-name">${item.name}</div>
                    <div class="cart-item-price">${formatRp(item.price * item.qty)}</div>
                </div>
                <div class="qty-ctrl">
                    <button class="qty-btn minus" onclick="changeQty('${item.id}', -1)">−</button>
                    <span class="qty-num">${item.qty}</span>
                    <button class="qty-btn plus" onclick="changeQty('${item.id}', 1)">+</button>
                </div>
                <button class="remove-btn" onclick="removeItem('${item.id}')" title="Hapus">
                    <i class="fas fa-times"></i>
                </button>
            </div>`;
      });
      cartItemsEl.innerHTML = html;
    }

    function doCheckout() {
      const items = Object.values(cart);
      if (items.length === 0) return;
      const method = document.getElementById('paymentMethod').value;
      showLoading(true);
      const payload = {
        items: items.map(i => ({
          id: i.id,
          qty: i.qty
        })),
        method: method,
        customer: document.getElementById('namaPelanggan').value.trim() || null,
      };
      fetch('{{ route('admin.orders.checkout') }}', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
          },
          body: JSON.stringify(payload),
        })
        .then(r => r.json())
        .then(data => {
          showLoading(false);
          if (!data.success) {
            Swal.fire('Error', data.message || 'Terjadi kesalahan.', 'error');
            return;
          }
          if (method === 'midtrans' && data.snap_token) {
            snap.pay(data.snap_token, {
              onSuccess: function(result) {
                fetch(`/midtrans/confirm/${data.order_id}`, {
                    method: 'POST',
                    headers: {
                      'Accept': 'application/json'
                    }
                  })
                  .finally(() => {
                    showNota(data.invoice, items, method, payload.customer);
                  });
              },
              onPending: function(result) {
                Swal.fire('Menunggu', 'Pembayaran sedang diproses.', 'info');
              },
              onError: function(result) {
                Swal.fire('Gagal', 'Pembayaran gagal.', 'error');
              },
              onClose: function() {
                Swal.fire('Info', 'Anda menutup popup pembayaran.', 'warning');
              }
            });
          } else {
            showNota(data.invoice, items, method, payload.customer);
          }
        })
        .catch(err => {
          showLoading(false);
          console.error(err);
          Swal.fire('Error', 'Koneksi bermasalah.', 'error');
        });
    }

    function showLoading(show) {
      document.getElementById('loadingOverlay').classList.toggle('show', show);
    }

    function showNota(invoice, items, metode, namaPelanggan) {
      const now = new Date();
      const tgl = now.toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
      const jam = now.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit'
      });
      document.getElementById('notaDate').textContent = tgl + ' · ' + jam;
      document.getElementById('notaInvoice').textContent = invoice;
      document.getElementById('notaTipe').textContent = 'Kasir / POS';
      document.getElementById('notaMetode').textContent = metode === 'cash' ? 'Cash / Tunai' : 'Midtrans (Digital)';
      const namaRow = document.getElementById('notaNamaRow');
      const namaEl = document.getElementById('notaNama');
      if (namaPelanggan) {
        namaEl.textContent = namaPelanggan;
        namaRow.style.display = 'flex';
      } else {
        namaRow.style.display = 'none';
      }
      let html = '';
      let total = 0;
      items.forEach(item => {
        const sub = item.price * item.qty;
        total += sub;
        html += `
            <div class="nota-item-row">
                <span class="item-name">${item.name}</span>
                <span class="item-qty">${item.qty}x</span>
                <span class="item-sub">${formatRp(sub)}</span>
            </div>`;
      });
      document.getElementById('notaItemsList').innerHTML = html;
      document.getElementById('notaTotal').textContent = formatRp(total);
      document.getElementById('notaOverlay').classList.add('show');
    }

    function closeNota() {
      window.location.reload();
    }

    function printNota() {
      window.print();
    }

    function formatRp(num) {
      return 'Rp ' + num.toLocaleString('id-ID');
    }

    document.querySelectorAll('.filter-tab').forEach(btn => {
      btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        document.querySelectorAll('.menu-card').forEach(card => {
          if (filter === 'all' || card.dataset.category === filter) {
            card.classList.remove('menu-hidden');
          } else {
            card.classList.add('menu-hidden');
          }
        });
      });
    });

    document.getElementById('menuSearch').addEventListener('input', function() {
      const q = this.value.toLowerCase();
      document.querySelectorAll('.menu-card').forEach(card => {
        const nama = card.dataset.name.toLowerCase();
        card.classList.toggle('menu-hidden', !nama.includes(q));
      });
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
