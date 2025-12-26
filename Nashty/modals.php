<!-- modals.php - Placeholder for now -->
<div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
    <div class="col-md-6">
        <label class="form-label">SKU</label>
        <div class="input-group">
            <input type="text" class="form-control" name="sku" id="sku" readonly placeholder="Generado autom.">
            <button class="btn btn-outline-secondary" type="button" id="btnGenSKU">Generar</button>
        </div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Código de Barras</label>
        <input type="text" class="form-control" name="barcode" id="barcode">
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Nombre Producto *</label>
    <input type="text" class="form-control" name="name" id="name" required>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <label class="form-label">Marca</label>
        <input type="text" class="form-control" name="brand" id="brand">
    </div>
    <div class="col-md-4">
        <label class="form-label">Talla</label>
        <input type="text" class="form-control" name="size" id="size">
    </div>
    <div class="col-md-4">
        <label class="form-label">Ubicación</label>
        <input type="text" class="form-control" name="location" id="location">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <label class="form-label">Costo *</label>
        <input type="number" step="0.01" class="form-control" name="cost_price" id="cost_price" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Precio Venta *</label>
        <input type="number" step="0.01" class="form-control" name="sale_price" id="sale_price" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Existencias *</label>
        <input type="number" class="form-control" name="stock" id="stock" required>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Marketplaces</label>
    <div class="d-flex gap-3 flex-wrap">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="marketplaces[]" value="Facebook" id="mp_fb">
            <label class="form-check-label" for="mp_fb">Facebook</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="marketplaces[]" value="Instagram" id="mp_ig">
            <label class="form-check-label" for="mp_ig">Instagram</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="marketplaces[]" value="MercadoLibre" id="mp_ml">
            <label class="form-check-label" for="mp_ml">MercadoLibre</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="marketplaces[]" value="Vinted" id="mp_vi">
            <label class="form-check-label" for="mp_vi">Vinted</label>
        </div>
    </div>
</div>

<div class="mb-3">
    <!-- modals.php - Placeholder for now -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="productModalLabel">Nuevo Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="productForm">
                        <input type="hidden" id="productId" name="id">
                        <input type="hidden" name="action" value="save_product">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">SKU</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="sku" id="sku" readonly
                                        placeholder="Generado autom.">
                                    <button class="btn btn-outline-secondary" type="button"
                                        id="btnGenSKU">Generar</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Código de Barras</label>
                                <input type="text" class="form-control" name="barcode" id="barcode">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre Producto *</label>
                            <input type="text" class="form-control" name="name" id="name" required>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Marca</label>
                                <input type="text" class="form-control" name="brand" id="brand">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Talla</label>
                                <input type="text" class="form-control" name="size" id="size">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Ubicación</label>
                                <input type="text" class="form-control" name="location" id="location">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Costo *</label>
                                <input type="number" step="0.01" class="form-control" name="cost_price" id="cost_price"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Precio Venta *</label>
                                <input type="number" step="0.01" class="form-control" name="sale_price" id="sale_price"
                                    required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Existencias *</label>
                                <input type="number" class="form-control" name="stock" id="stock" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Marketplaces</label>
                            <div class="d-flex gap-3 flex-wrap">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="marketplaces[]"
                                        value="Facebook" id="mp_fb">
                                    <label class="form-check-label" for="mp_fb">Facebook</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="marketplaces[]"
                                        value="Instagram" id="mp_ig">
                                    <label class="form-check-label" for="mp_ig">Instagram</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="marketplaces[]"
                                        value="MercadoLibre" id="mp_ml">
                                    <label class="form-check-label" for="mp_ml">MercadoLibre</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="marketplaces[]" value="Vinted"
                                        id="mp_vi">
                                    <label class="form-check-label" for="mp_vi">Vinted</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Detalles Adicionales</label>
                            <textarea class="form-control" name="details" id="details" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnSaveProduct">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sale Modal -->
    <div class="modal fade" id="saleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Venta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="saleForm">
                        <input type="hidden" id="saleProductId" name="product_id">
                        <input type="hidden" name="action" value="record_sale">

                        <div class="mb-3">
                            <p><strong>Producto:</strong> <span id="saleProductName"></span></p>
                            <p><strong>Stock Actual:</strong> <span id="saleProductStock"></span>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cantidad a Vender</label>
                            <input type="number" class="form-control" name="quantity" id="saleQuantity" min="1"
                                required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Precio Final (Unitario)</label>
                            <input type="number" step="0.01" class="form-control" name="price" <div class="modal fade"
                                id="saleModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Registrar Venta</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="saleForm">
                                            <input type="hidden" id="saleProductId" name="product_id">
                                            <input type="hidden" name="action" value="record_sale">

                                            <div class="mb-3">
                                                <p><strong>Producto:</strong> <span id="saleProductName"></span></p>
                                                <p><strong>Stock Actual:</strong> <span id="saleProductStock"></span>
                                                </p>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Cantidad a Vender</label>
                                                <input type="number" class="form-control" name="quantity"
                                                    id="saleQuantity" min="1" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Precio Final (Unitario)</label>
                                                <input type="number" step="0.01" class="form-control" name="price"
                                                    id="salePrice" required>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cancelar</button>
                                        <button type="button" class="btn btn-success" id="btnConfirmSale">Confirmar
                                            Venta</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sales Report Modal -->
                        <div class="modal fade" id="salesReportModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Historial de Ventas</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped" id="salesTable"
                                                style="width:100%">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Producto</th>
                                                        <th>Cant.</th>
                                                        <th>P. Unit.</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="salesTableBody">
                                                    <!-- Populated via JS -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Cerrar</button>
                                    </div>
                                </div>
                            </div>
                        </div>