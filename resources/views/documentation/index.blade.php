@extends('layouts.app')

@section('content')
<style>
.system-docs{padding:26px 0 40px}.docs-shell{display:grid;grid-template-columns:300px minmax(0,1fr);gap:22px}.docs-sidebar,.docs-content-card,.docs-hero,.docs-module-card{background:#fff;border:1px solid rgba(15,23,42,.06);border-radius:24px;box-shadow:0 16px 40px rgba(15,23,42,.06)}.docs-sidebar{position:sticky;top:90px;padding:22px;height:fit-content}.docs-sidebar h5{margin:0 0 14px;color:#0f172a;font-weight:900}.docs-nav{display:grid;gap:10px}.docs-nav a{display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:14px;text-decoration:none;color:#334155;background:#f8fafc;border:1px solid rgba(148,163,184,.12);font-weight:700}.docs-nav a:hover{background:color-mix(in srgb,var(--theme-default,#7367f0) 8%,#ffffff);color:var(--theme-default,#7367f0)}.docs-main{display:grid;gap:22px}.docs-hero{padding:28px}.docs-hero-badge{display:inline-flex;align-items:center;gap:10px;padding:8px 14px;border-radius:999px;background:color-mix(in srgb,var(--theme-default,#7367f0) 10%,#ffffff);color:var(--theme-default,#7367f0);font-size:.82rem;font-weight:800;letter-spacing:.04em;text-transform:uppercase}.docs-hero h3{margin:16px 0 10px;color:#0f172a;font-size:clamp(2rem,3vw,2.6rem);font-weight:900;letter-spacing:-.04em}.docs-hero p{margin:0;color:#64748b;font-size:1rem;line-height:1.8;max-width:980px}.docs-summary{margin-top:20px;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.docs-summary-card{padding:18px;border-radius:18px;background:#f8fafc;border:1px solid rgba(148,163,184,.12)}.docs-summary-card span{display:block;color:#64748b;font-size:.78rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px}.docs-summary-card strong{display:block;color:#0f172a;font-size:1.12rem}.docs-content-card{padding:26px}.docs-section+.docs-section{margin-top:28px;padding-top:28px;border-top:1px solid rgba(148,163,184,.14)}.docs-section h4{margin:0 0 8px;color:#0f172a;font-size:1.45rem;font-weight:900}.docs-section-intro{margin:0 0 18px;color:#64748b;line-height:1.8}.docs-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.docs-module-card{padding:20px;height:100%}.docs-module-card h5{margin:0 0 8px;color:#0f172a;font-size:1.08rem;font-weight:900}.docs-module-card p{margin:0 0 12px;color:#64748b;line-height:1.7}.docs-module-card ul,.docs-section ul{margin:0;padding-left:18px;color:#334155;line-height:1.75}.docs-module-card li+li,.docs-section li+li{margin-top:6px}.docs-steps{display:grid;gap:14px}.docs-step{padding:18px;border-radius:18px;background:#f8fafc;border:1px solid rgba(148,163,184,.12)}.docs-step strong{display:block;color:#0f172a;font-size:1rem;margin-bottom:8px}.docs-step p{margin:0;color:#64748b;line-height:1.75}.docs-table{width:100%;border-collapse:separate;border-spacing:0}.docs-table th,.docs-table td{padding:14px 16px;border-bottom:1px solid rgba(148,163,184,.12);vertical-align:top;text-align:left}.docs-table th{color:#0f172a;font-size:.82rem;text-transform:uppercase;letter-spacing:.08em;background:#f8fafc}.docs-table td{color:#475569;line-height:1.7}.docs-table tr:last-child td{border-bottom:0}.docs-inline-note{margin-top:16px;padding:16px 18px;border-radius:18px;background:color-mix(in srgb,var(--theme-default,#7367f0) 7%,#ffffff);border:1px solid color-mix(in srgb,var(--theme-default,#7367f0) 14%,#ffffff);color:#334155;line-height:1.75}.docs-mini-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.docs-mini-card{padding:18px;border-radius:18px;background:#f8fafc;border:1px solid rgba(148,163,184,.12)}.docs-mini-card h6{margin:0 0 8px;color:#0f172a;font-size:.98rem;font-weight:900}.docs-mini-card p{margin:0;color:#64748b;line-height:1.7}.docs-pill-row{display:flex;flex-wrap:wrap;gap:10px;margin-top:14px}.docs-pill{display:inline-flex;align-items:center;gap:8px;padding:9px 12px;border-radius:999px;background:#f8fafc;border:1px solid rgba(148,163,184,.12);color:#334155;font-weight:700}.docs-pill i{color:var(--theme-default,#7367f0)}@media (max-width:1199.98px){.docs-shell{grid-template-columns:1fr}.docs-sidebar{position:static}.docs-summary,.docs-mini-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (max-width:767.98px){.system-docs{padding:18px 0 32px}.docs-hero,.docs-content-card,.docs-sidebar,.docs-module-card{border-radius:18px}.docs-grid,.docs-summary,.docs-mini-grid{grid-template-columns:1fr}.docs-content-card,.docs-sidebar,.docs-hero{padding:18px}}
</style>

<div class="page-body system-docs">
    <div class="container-fluid">
        <div class="docs-shell">
            <aside class="docs-sidebar">
                <h5>{{ __('system_documentation') }}</h5>
                <div class="docs-nav">
                    <a href="#overview"><i class="fa fa-book"></i><span>Overview</span></a>
                    <a href="#admin-setup"><i class="fa fa-sliders"></i><span>Admin Setup</span></a>
                    <a href="#catalog"><i class="fa fa-tags"></i><span>Catalog Setup</span></a>
                    <a href="#orders"><i class="fa fa-bag-shopping"></i><span>Orders and Operations</span></a>
                    <a href="#staff-driver"><i class="fa fa-users"></i><span>Staff and Driver Portal</span></a>
                    <a href="#customer-flow"><i class="fa fa-user"></i><span>Customer Flow</span></a>
                    <a href="#maps-payments"><i class="fa fa-map-location-dot"></i><span>Maps and Payments</span></a>
                    <a href="#dashboard"><i class="fa fa-chart-column"></i><span>Dashboard and Reports</span></a>
                    <a href="#emails"><i class="fa fa-envelope"></i><span>Emails and Templates</span></a>
                    <a href="#best-practice"><i class="fa fa-circle-check"></i><span>Best Practice</span></a>
                </div>
            </aside>

            <div class="docs-main">
                <section class="docs-hero" id="overview">
                    <span class="docs-hero-badge"><i class="fa fa-book-open"></i> Complete Usage Guide</span>
                    <h3>{{ __('system_documentation') }}</h3>
                    <p>
                        This page explains the full food ordering system from admin setup to customer checkout, staff order handling,
                        driver delivery flow, route maps, invoices, email notifications, analytics, and customization settings.
                        Use it as the main manual for onboarding your team and understanding how every major part of the system works.
                    </p>

                    <div class="docs-summary">
                        <div class="docs-summary-card">
                            <span>Admin Side</span>
                            <strong>Configure products, users, branches, settings, permissions, and analytics.</strong>
                        </div>
                        <div class="docs-summary-card">
                            <span>Customer Side</span>
                            <strong>Browse products, register, order, pay, track orders, and manage profile data.</strong>
                        </div>
                        <div class="docs-summary-card">
                            <span>Staff Side</span>
                            <strong>Manage branch orders on the frontend, edit orders, assign drivers, and track routes.</strong>
                        </div>
                        <div class="docs-summary-card">
                            <span>Driver Side</span>
                            <strong>View only assigned orders, open route maps, and update order status.</strong>
                        </div>
                    </div>
                </section>

                <section class="docs-content-card">
                    <div class="docs-section" id="admin-setup">
                        <h4>1. Admin Setup and System Configuration</h4>
                        <p class="docs-section-intro">
                            The admin panel is the control center of the system. Use it first to prepare branches, products, users,
                            permissions, currencies, taxes, shipping rules, visual styling, and email behavior before going live.
                        </p>

                        <div class="docs-grid">
                            <div class="docs-module-card">
                                <h5>Roles and Permissions</h5>
                                <p>Create system access levels and decide what every user can open or manage.</p>
                                <ul>
                                    <li>Use <strong>Roles</strong> to create groups like Super Admin, Staff, Driver, Manager, or custom roles.</li>
                                    <li>Use <strong>Permissions</strong> to control who can view, add, edit, delete, or manage a module.</li>
                                    <li>Assign roles to users from the user management section.</li>
                                    <li>Use this when you want some users to manage products only, some to manage orders only, and some to access everything.</li>
                                </ul>
                            </div>

                            <div class="docs-module-card">
                                <h5>User Management</h5>
                                <p>Create admin users, staff accounts, and driver accounts from one place.</p>
                                <ul>
                                    <li>Add user name, email, password, image, status, and role.</li>
                                    <li>Attach one or multiple branches to each staff or driver through organization assignment.</li>
                                    <li>Staff users can see orders for their assigned branches.</li>
                                    <li>Drivers can only see orders that belong to their branches and are assigned to them.</li>
                                </ul>
                            </div>

                            <div class="docs-module-card">
                                <h5>Organizations, Countries, Regions, Locations</h5>
                                <p>These modules help define your delivery structure and branch coverage.</p>
                                <ul>
                                    <li>Create branches in <strong>Organizations</strong>.</li>
                                    <li>Add address, latitude, longitude, and active status for each branch.</li>
                                    <li>Use countries, regions, and locations to support address structure and branch grouping.</li>
                                    <li>Branch coordinates are used in staff and driver map routing.</li>
                                </ul>
                            </div>

                            <div class="docs-module-card">
                                <h5>Settings</h5>
                                <p>Use settings to customize system appearance and technical behavior.</p>
                                <ul>
                                    <li>Change admin theme colors used in the header, buttons, dashboard, and other admin UI elements.</li>
                                    <li>Configure sidebar colors, header colors, logos, favicon, and branded overlays.</li>
                                    <li>Configure login page appearance and frontend header and footer styling.</li>
                                    <li>Set timezone, metadata, and map provider options.</li>
                                    <li>Configure payment, mail, and license related options where available.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="docs-inline-note">
                            Recommended setup order: create roles and permissions, create branches, create staff and drivers, then move to product and order configuration.
                        </div>
                    </div>

                    <div class="docs-section" id="catalog">
                        <h4>2. Product and Checkout Catalog Setup</h4>
                        <p class="docs-section-intro">
                            These modules define what customers can order and how pricing is calculated at checkout.
                        </p>

                        <div class="docs-mini-grid">
                            <div class="docs-mini-card">
                                <h6>Categories</h6>
                                <p>Create menu groups such as Burgers, Pizza, Drinks, or Desserts to keep the storefront organized.</p>
                            </div>
                            <div class="docs-mini-card">
                                <h6>Products</h6>
                                <p>Add the actual food items with title, description, image, base price, status, and category assignment.</p>
                            </div>
                            <div class="docs-mini-card">
                                <h6>Addons</h6>
                                <p>Create extra options such as cheese, sauces, drink upgrades, sizes, or meal extras and link them to products.</p>
                            </div>
                            <div class="docs-mini-card">
                                <h6>Taxes</h6>
                                <p>Set tax rules used during pricing calculation so the system can calculate VAT or other tax amounts correctly.</p>
                            </div>
                            <div class="docs-mini-card">
                                <h6>Shipping Fees</h6>
                                <p>Configure delivery charges that are added for delivery orders. Pickup orders can remain without delivery fees.</p>
                            </div>
                            <div class="docs-mini-card">
                                <h6>Currencies</h6>
                                <p>Manage active currencies and define the base currency used for storefront pricing and totals.</p>
                            </div>
                        </div>
                    </div>

                    <div class="docs-section" id="orders">
                        <h4>3. Orders and Daily Operations</h4>
                        <p class="docs-section-intro">
                            Orders can be managed from the admin side and from the frontend staff portal. The admin side shows all system orders, while staff users see orders based on assigned branches.
                        </p>

                        <table class="docs-table">
                            <thead>
                                <tr>
                                    <th>Feature</th>
                                    <th>What it does</th>
                                    <th>How to use it</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Order Listing</td>
                                    <td>Shows all orders with newest orders first, customer info, branch, payment details, order type, status, total, and actions.</td>
                                    <td>Use filters or search to find the right order quickly. Open edit, invoice, route, or status actions from the action buttons.</td>
                                </tr>
                                <tr>
                                    <td>Status Update</td>
                                    <td>Moves an order between statuses such as pending payment, processing, shipped, delivered, cancelled, returned, or failed.</td>
                                    <td>Open the status modal and choose the next status. Status changes can trigger email notifications to the customer.</td>
                                </tr>
                                <tr>
                                    <td>Edit Order</td>
                                    <td>Allows authorized users to update line items, remove items, and add new products with addons.</td>
                                    <td>Open the edit page, update the order contents, then save. The system recalculates subtotal, shipping, tax, and grand total automatically.</td>
                                </tr>
                                <tr>
                                    <td>Invoice View and PDF</td>
                                    <td>Shows a printable invoice preview and allows PDF download.</td>
                                    <td>Use the invoice button to preview the invoice in a popup and the PDF button to download it.</td>
                                </tr>
                                <tr>
                                    <td>Delete Order</td>
                                    <td>Deletes an order and its related items where allowed.</td>
                                    <td>Available on admin side and preserved for future use on staff side through commented action logic.</td>
                                </tr>
                                <tr>
                                    <td>Assign Driver</td>
                                    <td>Assigns an order to a driver.</td>
                                    <td>Click assign driver. The dropdown only shows drivers who have the Driver role and belong to the same branch as the order.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="docs-section" id="staff-driver">
                        <h4>4. Staff and Driver Frontend Portal</h4>
                        <p class="docs-section-intro">
                            Staff and drivers do not work through the admin panel. They log in from the frontend login page and are redirected into a dedicated frontend operations dashboard.
                        </p>

                        <div class="docs-grid">
                            <div class="docs-module-card">
                                <h5>Login Tabs</h5>
                                <p>The frontend login page includes three login tabs: Customer, Staff, and Driver.</p>
                                <ul>
                                    <li>Customer login checks the customer table.</li>
                                    <li>Staff login checks the users table with role <strong>Staff</strong>.</li>
                                    <li>Driver login checks the users table with role <strong>Driver</strong>.</li>
                                    <li>Customer is selected by default on the login screen.</li>
                                </ul>
                            </div>

                            <div class="docs-module-card">
                                <h5>Branch Based Visibility</h5>
                                <p>Branch assignment controls what staff and drivers can see.</p>
                                <ul>
                                    <li>A staff user sees orders from one or many assigned branches.</li>
                                    <li>A driver sees only orders inside their assigned branches and only when those orders are assigned to that specific driver.</li>
                                    <li>If no branch is assigned to the user, no operational orders will appear.</li>
                                </ul>
                            </div>

                            <div class="docs-module-card">
                                <h5>Staff Capabilities</h5>
                                <p>Staff users have a branch operations dashboard on the frontend side.</p>
                                <ul>
                                    <li>View branch order cards and filtered listings.</li>
                                    <li>Edit orders from the frontend full-page edit screen.</li>
                                    <li>View and download invoices.</li>
                                    <li>Open route maps for single orders.</li>
                                    <li>Open route planner for multiple orders.</li>
                                    <li>Assign drivers to orders from the frontend listing.</li>
                                    <li>Update order status.</li>
                                </ul>
                            </div>

                            <div class="docs-module-card">
                                <h5>Driver Capabilities</h5>
                                <p>Drivers have a restricted frontend portal designed for delivery execution only.</p>
                                <ul>
                                    <li>Can view assigned orders only.</li>
                                    <li>Can open map and route information.</li>
                                    <li>Can update order status.</li>
                                    <li>Cannot edit the order.</li>
                                    <li>Cannot view invoice, download invoice, or delete the order.</li>
                                </ul>
                            </div>
                        </div>

                        <div class="docs-steps mt-4">
                            <div class="docs-step">
                                <strong>Staff workflow example</strong>
                                <p>Log in from the Staff tab, open the frontend dashboard, click a status card to filter orders, update an order if needed, assign a driver, review the route, and then continue monitoring the order until completion.</p>
                            </div>
                            <div class="docs-step">
                                <strong>Driver workflow example</strong>
                                <p>Log in from the Driver tab, review only assigned orders, open the route popup or route planner, choose branch or current location as the starting point, and update the order status as the delivery progresses.</p>
                            </div>
                        </div>
                    </div>

                    <div class="docs-section" id="customer-flow">
                        <h4>5. Customer Frontend Flow</h4>
                        <p class="docs-section-intro">
                            Customers use the public website to browse products, manage their account, place orders, and track activity after checkout.
                        </p>

                        <div class="docs-pill-row">
                            <span class="docs-pill"><i class="fa fa-store"></i> Browse product catalog</span>
                            <span class="docs-pill"><i class="fa fa-cart-shopping"></i> Add to cart with addons</span>
                            <span class="docs-pill"><i class="fa fa-user-plus"></i> Register or log in</span>
                            <span class="docs-pill"><i class="fa fa-credit-card"></i> Checkout and pay</span>
                            <span class="docs-pill"><i class="fa fa-receipt"></i> View dashboard orders</span>
                        </div>

                        <div class="docs-grid mt-4">
                            <div class="docs-module-card">
                                <h5>Registration and Profile</h5>
                                <p>Customers can create a frontend account and later manage profile details from the dashboard.</p>
                                <ul>
                                    <li>Stores customer name, contact details, address, and account data.</li>
                                    <li>Customers can update profile information after login.</li>
                                </ul>
                            </div>

                            <div class="docs-module-card">
                                <h5>Cart and Checkout</h5>
                                <p>The cart supports products, addons, tax, shipping, and multiple payment methods.</p>
                                <ul>
                                    <li>Delivery summary can calculate route-aware delivery information.</li>
                                    <li>Payment intent creation supports gateway based payments.</li>
                                    <li>Cash on delivery and other configured methods can move orders directly into processing.</li>
                                </ul>
                            </div>

                            <div class="docs-module-card">
                                <h5>Customer Dashboard</h5>
                                <p>After login, the customer can see order history and open order detail pages.</p>
                                <ul>
                                    <li>Review status of submitted orders.</li>
                                    <li>Pay balance on updated orders where extra payment is required.</li>
                                    <li>See changes communicated by email and reflected in the order record.</li>
                                </ul>
                            </div>

                            <div class="docs-module-card">
                                <h5>Address and Map Helpers</h5>
                                <p>Address search, geocoding, and reverse geocoding help customers fill valid delivery information.</p>
                                <ul>
                                    <li>Improves delivery accuracy.</li>
                                    <li>Stores coordinates for map based delivery planning later in staff and driver portals.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="docs-section" id="maps-payments">
                        <h4>6. Maps, Routing, and Payment Features</h4>
                        <p class="docs-section-intro">
                            The system includes map-based route tools for staff and drivers and payment support for frontend ordering and balance collection.
                        </p>

                        <div class="docs-grid">
                            <div class="docs-module-card">
                                <h5>Single Order Route Popup</h5>
                                <p>Available in the staff and driver order listing.</p>
                                <ul>
                                    <li>Shows branch details, customer details, coordinates, and distance.</li>
                                    <li>Displays shortest available route.</li>
                                    <li>Supports map marker popups with branch or customer information.</li>
                                    <li>Supports both <strong>Leaflet</strong> and <strong>Google Maps</strong>.</li>
                                    <li>Supports switching the origin between branch and current live location.</li>
                                </ul>
                            </div>

                            <div class="docs-module-card">
                                <h5>Route Planner Popup</h5>
                                <p>Shows all mappable orders together in one planner view.</p>
                                <ul>
                                    <li>Displays all assigned order locations for the selected filter.</li>
                                    <li>Draws shortest path for each order.</li>
                                    <li>Allows branch origin or current location origin.</li>
                                    <li>Supports both Leaflet and Google Maps.</li>
                                </ul>
                            </div>

                            <div class="docs-module-card">
                                <h5>Payment Processing</h5>
                                <p>Customers can pay during checkout or later if an order is updated and a remaining balance exists.</p>
                                <ul>
                                    <li>Supports payment intent flows for online gateways.</li>
                                    <li>Order status is set automatically depending on payment method and payment status.</li>
                                    <li>Balance payment links can be sent when admin or staff updates increase the order total.</li>
                                </ul>
                            </div>

                            <div class="docs-module-card">
                                <h5>Map Provider Selection</h5>
                                <p>The system can use different map engines depending on settings.</p>
                                <ul>
                                    <li>Leaflet can render map previews and shortest route overlays.</li>
                                    <li>Google Maps can also render route maps when API configuration is provided.</li>
                                    <li>Frontend route features follow the selected map provider automatically.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="docs-section" id="dashboard">
                        <h4>7. Dashboard and Reporting</h4>
                        <p class="docs-section-intro">
                            The admin dashboard is a full business overview screen for operations and management insight.
                        </p>

                        <div class="docs-grid">
                            <div class="docs-module-card">
                                <h5>Admin Business Dashboard</h5>
                                <p>Shows real system analytics using the selected date range.</p>
                                <ul>
                                    <li>Revenue and order timeline.</li>
                                    <li>Gross revenue, net sales, shipping cost, sold items, and new customers cards.</li>
                                    <li>Order status chart and payment method chart.</li>
                                    <li>Branch performance and top selling product charts.</li>
                                    <li>Customer growth, top customers, and recent user activity.</li>
                                    <li>Uses admin theme colors configured in settings.</li>
                                </ul>
                            </div>

                            <div class="docs-module-card">
                                <h5>Visit Analytics</h5>
                                <p>Super Admin can also review portal visit activity.</p>
                                <ul>
                                    <li>Total visits and unique visitors.</li>
                                    <li>Visit chart for the selected period.</li>
                                    <li>Visitor countries and top traffic locations.</li>
                                </ul>
                            </div>

                            <div class="docs-module-card">
                                <h5>Staff Dashboard Cards</h5>
                                <p>The staff frontend dashboard has status summary cards and filtered listings.</p>
                                <ul>
                                    <li>Total orders.</li>
                                    <li>Pending payment, processing, shipped, delivered, cancelled, returned, and failed.</li>
                                    <li>Click a card to filter the table below.</li>
                                </ul>
                            </div>

                            <div class="docs-module-card">
                                <h5>Admin Order Monitoring</h5>
                                <p>Use admin manage orders as the master operational order screen for the whole system.</p>
                                <ul>
                                    <li>New orders appear first in the listing.</li>
                                    <li>Admin can see all branches and all orders.</li>
                                    <li>Assign drivers according to order branch.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="docs-section" id="emails">
                        <h4>8. Email Templates and Notifications</h4>
                        <p class="docs-section-intro">
                            Email communication is integrated into the order lifecycle so customers stay informed.
                        </p>

                        <div class="docs-grid">
                            <div class="docs-module-card">
                                <h5>Email Template Management</h5>
                                <p>Use the email template section to configure outgoing email content.</p>
                                <ul>
                                    <li>Edit template messaging from the admin panel.</li>
                                    <li>Send test emails to verify mail settings and layout.</li>
                                </ul>
                            </div>

                            <div class="docs-module-card">
                                <h5>Automatic Order Emails</h5>
                                <p>Order status and payment updates can send automatic notifications.</p>
                                <ul>
                                    <li>Status email when order status changes.</li>
                                    <li>Balance payment email when order total increases after edit.</li>
                                    <li>Customer receives the correct email based on the update event.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="docs-section" id="best-practice">
                        <h4>9. Recommended Usage and Best Practice</h4>
                        <p class="docs-section-intro">
                            Use the following working method to keep the system clean, secure, and easy for the team.
                        </p>

                        <div class="docs-steps">
                            <div class="docs-step">
                                <strong>Step 1: Build the business structure first</strong>
                                <p>Create branches, assign users to branches, then create products, taxes, shipping, and currencies before accepting live orders.</p>
                            </div>
                            <div class="docs-step">
                                <strong>Step 2: Keep staff and driver roles separate</strong>
                                <p>Staff should handle editing, invoices, assignments, and operational control. Drivers should focus only on assigned delivery work and status updates.</p>
                            </div>
                            <div class="docs-step">
                                <strong>Step 3: Keep map and branch data accurate</strong>
                                <p>Branch coordinates and customer delivery coordinates directly affect route quality. If maps look wrong, verify saved latitudes and longitudes first.</p>
                            </div>
                            <div class="docs-step">
                                <strong>Step 4: Use the dashboard for review, not only the order list</strong>
                                <p>The dashboard helps identify busy periods, strong products, active branches, and customer behavior trends that are not obvious from the order table alone.</p>
                            </div>
                            <div class="docs-step">
                                <strong>Step 5: Review permissions regularly</strong>
                                <p>When new modules or staff roles are added, verify permission and sidebar visibility rules so users only access what they should use.</p>
                            </div>
                        </div>

                        <div class="docs-inline-note">
                            If you want this documentation page to support multiple languages as well, the next step is to move these new help texts into your language files and replace the hardcoded content with translation keys.
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection
