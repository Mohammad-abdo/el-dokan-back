/* eslint-disable no-console */

const fs = require("fs");
const path = require("path");
const puppeteer = require("puppeteer");

function escapeHtml(value) {
  const v = value === null || value === undefined ? "" : String(value);
  return v
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function formatMoney(value) {
  const num = Number(value ?? 0);
  const safe = Number.isFinite(num) ? num : 0;
  return new Intl.NumberFormat("en-US", { style: "currency", currency: "USD" }).format(safe);
}

function orderStatusBadge(status) {
  const s = String(status ?? "").toLowerCase();
  if (s === "delivered") return { label: "Completed", className: "completed" };
  if (s === "cancelled") return { label: "Cancelled", className: "cancelled" };
  return { label: "Pending", className: "pending" };
}

function stockStatusBadge(stockQuantity) {
  const q = Number(stockQuantity ?? 0);
  const safe = Number.isFinite(q) ? q : 0;
  if (safe <= 0) return { label: "Out", className: "out-stock" };
  if (safe <= 10) return { label: "Low", className: "low-stock" };
  return { label: "In Stock", className: "in-stock" };
}

function imgTag(src, alt) {
  const safeSrc = src ? escapeHtml(src) : "";
  if (!safeSrc) {
    return `<div style="width:28px;height:28px;border:1px solid #e5e7eb;border-radius:8px;background:#f9fafb;"></div>`;
  }
  return `<img class="thumb" src="${safeSrc}" alt="${escapeHtml(alt || "Product")}"/>`;
}

function buildOrdersTable(orders) {
  const rows = (orders ?? [])
    .map((o) => {
      const status = orderStatusBadge(o.status);
      const orderedAt = o.ordered_at ? escapeHtml(new Date(o.ordered_at).toLocaleString()) : "—";
      const items = Array.isArray(o.items) ? o.items : [];

      const itemsHtml = items
        .map((it) => {
          const p = it.companyProduct || {};
          const name = escapeHtml(p.name || "—");
          const sku = p.sku ? ` <span class="muted">SKU: ${escapeHtml(p.sku)}</span>` : "";
          const qty = escapeHtml(it.quantity ?? 0);
          const unit = formatMoney(it.unit_price ?? 0);
          const total = formatMoney(it.total_price ?? 0);
          const firstImg = p.first_image_url || (Array.isArray(p.images) ? p.images[0] : null);

          return `
            <div class="item-line">
              ${imgTag(firstImg, p.name || "Product")}
              <div class="item-text">
                <div class="item-name">${name}${sku}</div>
                <div class="item-sub">Qty: ${qty} • Unit: ${escapeHtml(unit)} • Total: ${escapeHtml(total)}</div>
              </div>
            </div>
          `;
        })
        .join("");

      return `
        <tr class="row-avoid-break">
          <td>${escapeHtml(o.order_number || o.id)}</td>
          <td>
            <span class="badge ${status.className}">${status.label}</span>
          </td>
          <td class="text-right">${escapeHtml(formatMoney(o.total_amount ?? o.total ?? 0))}</td>
          <td>${orderedAt}</td>
          <td class="items-cell">${itemsHtml || `<div class="muted">No items</div>`}</td>
        </tr>
      `;
    })
    .join("");

  return `
    <table class="pdf-table">
      <thead>
        <tr>
          <th>Order</th>
          <th>Status</th>
          <th class="text-right">Total</th>
          <th>Date</th>
          <th>Items</th>
        </tr>
      </thead>
      <tbody>
        ${rows}
      </tbody>
    </table>
  `;
}

function buildProductsTable(products) {
  const rows = (products ?? [])
    .map((p) => {
      const firstImg = p.first_image_url || (Array.isArray(p.images) ? p.images[0] : null);
      const stock = stockStatusBadge(p.stock_quantity);
      return `
        <tr class="row-avoid-break">
          <td>${imgTag(firstImg, p.name || "Product")}</td>
          <td>
            <div style="font-weight:700;">${escapeHtml(p.name || "—")}</div>
            <div class="muted" style="font-size:11px;">${p.sku ? `SKU: ${escapeHtml(p.sku)}` : ""}</div>
          </td>
          <td class="text-right">${escapeHtml(formatMoney(p.unit_price ?? 0))}</td>
          <td>
            <span class="badge ${stock.className}">${stock.label}</span>
          </td>
        </tr>
      `;
    })
    .join("");

  return `
    <table class="pdf-table">
      <thead>
        <tr>
          <th>Image</th>
          <th>Product</th>
          <th class="text-right">Price</th>
          <th>Stock</th>
        </tr>
      </thead>
      <tbody>
        ${rows}
      </tbody>
    </table>
  `;
}

function buildPlansTable(plans) {
  const rows = (plans ?? [])
    .map((pl) => {
      const isActive = pl.is_active === false || pl.is_active === 0;
      const badge = isActive
        ? `<span class="badge cancelled" style="background:#f3f4f6;color:#111827;">Inactive</span>`
        : `<span class="badge completed">Active</span>`;

      return `
        <tr class="row-avoid-break">
          <td>
            <div style="font-weight:700;">${escapeHtml(pl.name_ar || pl.name || "—")}</div>
            <div class="muted" style="font-size:11px;">${pl.slug ? `/${escapeHtml(pl.slug)}` : ""}</div>
          </td>
          <td class="text-right">${escapeHtml(pl.max_products ?? 0)}</td>
          <td class="text-right">${escapeHtml(pl.max_branches ?? 0)}</td>
          <td class="text-right">${escapeHtml(pl.max_representatives ?? 0)}</td>
          <td class="text-right">${escapeHtml(formatMoney(pl.price ?? 0))}</td>
          <td>${badge}</td>
        </tr>
      `;
    })
    .join("");

  return `
    <table class="pdf-table">
      <thead>
        <tr>
          <th>Plan</th>
          <th class="text-right">Max Products</th>
          <th class="text-right">Max Branches</th>
          <th class="text-right">Max Reps</th>
          <th class="text-right">Price</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        ${rows}
      </tbody>
    </table>
  `;
}

function buildRepresentativesTable(reps) {
  const rows = (reps ?? [])
    .map((r) => {
      const status = String(r.status ?? "");
      const badgeClass =
        status === "active"
          ? "completed"
          : status === "suspended"
            ? "cancelled"
            : "pending";
      const badgeLabel = status || "pending_approval";
      const user = r.user || {};

      return `
        <tr class="row-avoid-break">
          <td>${escapeHtml(r.employee_id ?? "—")}</td>
          <td>${escapeHtml(r.territory ?? "—")}</td>
          <td>
            <span class="badge ${badgeClass}">${escapeHtml(badgeLabel)}</span>
          </td>
          <td>
            <div style="font-weight:700;">${escapeHtml(user.name || user.username || user.email || "—")}</div>
            <div class="muted" style="font-size:11px;">${user.email ? escapeHtml(user.email) : ""}</div>
          </td>
          <td>${r.created_at ? escapeHtml(new Date(r.created_at).toLocaleString()) : "—"}</td>
        </tr>
      `;
    })
    .join("");

  return `
    <table class="pdf-table">
      <thead>
        <tr>
          <th>Employee ID</th>
          <th>Territory</th>
          <th>Status</th>
          <th>User</th>
          <th>Created</th>
        </tr>
      </thead>
      <tbody>
        ${rows}
      </tbody>
    </table>
  `;
}

function buildVisitsTable(visits) {
  const rows = (visits ?? [])
    .map((v) => {
      const status = String(v.status ?? "");
      const badge =
        status === "approved" || status === "completed"
          ? `<span class="badge completed">${escapeHtml(status)}</span>`
          : status === "rejected"
            ? `<span class="badge cancelled">${escapeHtml(status)}</span>`
            : `<span class="badge pending">${escapeHtml(status || "pending")}</span>`;

      return `
        <tr class="row-avoid-break">
          <td>${v.visit_date ? escapeHtml(new Date(v.visit_date).toLocaleDateString()) : "—"}</td>
          <td>${v.visit_time ? escapeHtml(String(v.visit_time)) : "—"}</td>
          <td>${escapeHtml(v.doctor?.name || v.doctor?.fullName || v.doctor?.username || "—")}</td>
          <td>
            <div style="max-width: 360px; word-wrap: break-word;">${escapeHtml(v.purpose || "—")}</div>
          </td>
          <td>${badge}</td>
        </tr>
      `;
    })
    .join("");

  return `
    <table class="pdf-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Time</th>
          <th>Doctor</th>
          <th>Purpose</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        ${rows}
      </tbody>
    </table>
  `;
}

function fillTemplate(template, map) {
  let out = template;
  for (const [k, v] of Object.entries(map)) {
    out = out.replaceAll(k, v);
  }
  return out;
}

async function main() {
  const inputPath = process.argv[2];
  const outputPath = process.argv[3];

  if (!inputPath || !outputPath) {
    console.error("Usage: node generateFullDataExportPdf.js <payload.json> <out.pdf>");
    process.exit(1);
  }

  const payload = JSON.parse(fs.readFileSync(inputPath, "utf8"));

  const companyName = payload?.meta?.company?.name || "Company";
  const companyLogo = payload?.meta?.company?.logo_url || "";
  const exportDate = payload?.meta?.exportDate ? new Date(payload.meta.exportDate).toLocaleString() : "";

  const ordersTable = buildOrdersTable(payload?.company_orders);
  const productsTable = buildProductsTable(payload?.company_products);
  const plansTable = buildPlansTable(payload?.company_plans);
  const repsTable = buildRepresentativesTable(payload?.representatives);
  const visitsTable = buildVisitsTable(payload?.visits);

  const templatePath = path.join(__dirname, "templates", "full-data-export-report.html");
  const template = fs.readFileSync(templatePath, "utf8");

  const html = fillTemplate(template, {
    "__COMPANY_NAME__": escapeHtml(companyName),
    "__COMPANY_LOGO__": escapeHtml(companyLogo),
    "__EXPORT_DATE__": escapeHtml(exportDate),
    "__ORDERS_TABLE__": ordersTable,
    "__PRODUCTS_TABLE__": productsTable,
    "__PLANS_TABLE__": plansTable,
    "__REPRESENTATIVES_TABLE__": repsTable,
    "__VISITS_TABLE__": visitsTable,
  });

  const footer = `
    <div style="width:100%; font-size:10px; padding:0 16px; color:#6b7280; display:flex; justify-content:space-between; align-items:center;">
      <span>${escapeHtml(companyName)}</span>
      <span>${escapeHtml(exportDate)}</span>
      <span style="margin-left:auto;">
        Page <span class="pageNumber"></span> / <span class="totalPages"></span>
      </span>
    </div>
  `;

  const browser = await puppeteer.launch({
    headless: "new",
    args: [
      "--no-sandbox",
      "--disable-setuid-sandbox",
    ],
  });

  try {
    const page = await browser.newPage();
    await page.setViewport({ width: 1365, height: 768 });
    await page.setContent(html, { waitUntil: ["domcontentloaded", "networkidle0"] });

    await page.pdf({
      path: outputPath,
      format: "A4",
      printBackground: true,
      displayHeaderFooter: true,
      headerTemplate: "<div></div>",
      footerTemplate: footer,
      margin: { top: "44px", bottom: "54px", left: "20px", right: "20px" },
    });
  } finally {
    await browser.close();
  }
}

main().catch((e) => {
  console.error(e);
  process.exit(1);
});

