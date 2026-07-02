<?= $this->extend('layout') ?>
<?= $this->section('content') ?>

<div class="row">
    <div class="col-lg-6">
        <h5>Detail Pesanan</h5>
        <?= form_open('buy', 'class="row g-3"') ?>

        <?= form_hidden('username', session()->get('username')) ?>

        <div class="col-12">
            <?= form_label('Nama', 'nama', ['class' => 'form-label']) ?>
            <?= form_input([
                'name'     => 'nama',
                'id'       => 'nama',
                'class'    => 'form-control',
                'value'    => session()->get('username'),
                'readonly' => true]) ?>
        </div>
        <div class="col-12">
            <?= form_label('Alamat', 'alamat', ['class' => 'form-label']) ?>
            <?= form_input([
                'name'  => 'alamat',
                'id'    => 'alamat',
                'class' => 'form-control']) ?>
        </div>
        <div class="col-12">
            <?= form_label('Kelurahan', 'kelurahan', ['class' => 'form-label']) ?>
            <?= form_dropdown('kelurahan', [], '', ['id' => 'kelurahan', 'class' => 'form-control']) ?>
        </div>
        <div class="col-12">
            <?= form_label('Layanan', 'layanan', ['class' => 'form-label']) ?>
            <?= form_dropdown('layanan', [], '', ['id' => 'layanan', 'class' => 'form-control']) ?>
        </div>
        <div class="col-12">
            <?= form_label('Ongkir', 'ongkir', ['class' => 'form-label']) ?>
            <?= form_input([
                'name'     => 'ongkir',
                'id'       => 'ongkir',
                'class'    => 'form-control',
                'readonly' => true]) ?>
        </div>

        <!-- INPUT VOUCHER BARU -->
        <div class="col-12">
            <?= form_label('Kode Voucher', 'voucher_code', ['class' => 'form-label']) ?>
            <?= form_input([
                'name'        => 'voucher_code',
                'id'          => 'voucher_code',
                'class'       => 'form-control',
                'placeholder' => 'Masukkan kode voucher']) ?>
            <small class="text-muted">Tersedia: FLASH10, FLASH15, MEMBER20</small>
        </div>

        <div class="col-12">
            <?= form_submit('submit', 'Buat Pesanan', ['class' => 'btn btn-primary']) ?>
        </div>

        <?= form_close() ?>
    </div>

    <div class="col-lg-6">
        <h5>Ringkasan Pesanan</h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Jumlah</th>
                    <th>Sub Total</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($items)) : foreach ($items as $item) : ?>
                <tr>
                    <td><?= $item['name'] ?></td>
                    <td><?= number_to_currency($item['price'], 'IDR') ?></td>
                    <td><?= $item['qty'] ?></td>
                    <td><?= number_to_currency($item['price'] * $item['qty'], 'IDR') ?></td>
                </tr>
                <?php endforeach; endif; ?>

                <tr>
                    <td colspan="2"></td>
                    <td>Subtotal</td>
                    <td><?= number_to_currency($total, 'IDR') ?></td>
                </tr>
                <tr class="text-danger">
                    <td colspan="2"></td>
                    <td>Diskon Voucher</td>
                    <td><span id="diskon_voucher_display">-IDR 0</span></td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td>PPN (11%)</td>
                    <td><span id="ppn_display">IDR 0</span></td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td>Biaya Admin</td>
                    <td><span id="biaya_admin_display">IDR 0</span></td>
                </tr>
                <tr class="fw-bold">
                    <td colspan="2"></td>
                    <td>Subtotal (+PPN+Admin-Voucher)</td>
                    <td><span id="subtotal_display">IDR 0</span></td>
                </tr>
                <tr class="fw-bold text-primary">
                    <td colspan="2"></td>
                    <td>Grand Total (incl. Ongkir)</td>
                    <td><span id="grand_total_display">IDR 0</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('script') ?>
<script>
$(document).ready(function() {
    let ongkir = 0;
    let subtotal = <?= $total ?>;

    const vouchers = {
        'FLASH10': 0.10,
        'FLASH15': 0.15,
        'MEMBER20': 0.20
    };

    function hitungTotal() {
        let voucher_code = $('#voucher_code').val().toUpperCase();
        let diskon_pct = vouchers[voucher_code] || 0;
        let diskon_voucher = subtotal * diskon_pct;
        let ppn = subtotal * 0.11;
        let biaya_admin = 0;

        if (subtotal <= 20000000) {
            biaya_admin = subtotal * 0.006;
        } else if (subtotal <= 40000000) {
            biaya_admin = subtotal * 0.008;
        } else {
            biaya_admin = subtotal * 0.01;
        }

        let subtotal_after = subtotal - diskon_voucher + ppn + biaya_admin;
        let grand_total = subtotal_after + ongkir;

        $("#ongkir").val(ongkir);
        $('#diskon_voucher_display').text('-IDR ' + diskon_voucher.toLocaleString('id-ID'));
        $('#ppn_display').text('IDR ' + ppn.toLocaleString('id-ID'));
        $('#biaya_admin_display').text('IDR ' + biaya_admin.toLocaleString('id-ID'));
        $('#subtotal_display').text('IDR ' + subtotal_after.toLocaleString('id-ID'));
        $('#grand_total_display').text('IDR ' + grand_total.toLocaleString('id-ID'));
    }

    hitungTotal();

    // Update otomatis saat voucher diketik
    $('#voucher_code').on('input', function() {
        hitungTotal();
    });

    $('#kelurahan').select2({
        placeholder: 'Cari daerah tujuan',
        minimumInputLength: 3,
        ajax: {
            url: '<?= site_url('ajax/destinations') ?>',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return { q: params.term };
            },
            processResults: function(data) {
                return data;
            },
            cache: true
        }
    });

    $("#kelurahan").on('change', function() {
        let id_kelurahan = $(this).val();
        $("#layanan").empty();
        ongkir = 0;
        hitungTotal();

        $.ajax({
            url: "<?= site_url('ajax/costs') ?>",
            dataType: "json",
            data: { destination: id_kelurahan },
            success: function(data) {
                data.forEach(function(item) {
                    $("#layanan").append(
                        $('<option>', {
                            value: item.cost,
                            text: `${item.description} (${item.service}) : estimasi ${item.etd}`
                        })
                    );
                });
                ongkir = parseInt($("#layanan").val()) || 0;
                hitungTotal();
            }
        });
    });

    $("#layanan").on('change', function() {
        ongkir = parseInt($(this).val()) || 0;
        hitungTotal();
    });
});
</script>
<?= $this->endSection() ?>