<?php

use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CompoundController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockAlertController;
use App\Http\Controllers\SupplierController;

//use App\Http\Controllers\CompoundProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
foreach (glob(__DIR__ . '/api/*.php') as $filename) {
    require_once $filename;
}

//Route::post('/register', [UserAuthController::class, 'register'])->name('register');
//Route::post('/login', [UserAuthController::class, 'login'])->name('login');
//Route::get('/user', [UserAuthController::class, 'user'])->name('user');
//Route::group([
//    'middleware' => 'auth:api'
//], function () {
//    //Product
//    Route::post("product/add", [ProductController::class, "saveProduct"])->middleware('admin_access')->name("addProduct");
//    Route::get("products", [ProductController::class, "getProducts"])->name("products");
//    Route::post("product/update/{id}", [ProductController::class, "updateProduct"])->middleware('admin_access')->name("editProduct");
//    Route::get("product/view/{id}", [ProductController::class, "getProduct"])->name("viewProduct");
//    Route::get("product/name/{product_name}", [ProductController::class, "getByName"])->name("viewProductName");
//    Route::get("product/search/{product_name}", [ProductController::class, "getByChar"])->name("searchProductName");
//    Route::delete("product/delete/{id}", [ProductController::class, "deleteProduct"])->middleware('admin_access')->name("deleteProduct");
//
//    //Stock Alert
//    Route::get('/stockAlert', [StockAlertController::class, 'checkStockAlert']);
//
//    //compound product
//    Route::post('/compound-products', [CompoundController::class, 'store'])->middleware('admin_access');
//    Route::get('/compound-products/{id}', [CompoundController::class, 'show']);
//    Route::delete("compound/delete/{id}", [CompoundController::class, "delete"])->middleware('admin_access')->name("deleteCompound");
//    Route::get("compounds", [CompoundController::class, "getCompounds"])->name("compounds");
//    Route::get("compound/search/{compound_name}", [CompoundController::class, "getByChar"])->name("searchCompoundName");
//    Route::post('/compound-update/{id}', [CompoundController::class, 'update'])->middleware('admin_access');
//
//    //Customer
//    Route::post("customer/add", [CustomerController::class, "saveCustomer"])->middleware('admin_access')->name("add-customer");
//    Route::get("customers", [CustomerController::class, "getCustomers"])->name("all-customers");
//    Route::get("customer/view/{id}", [CustomerController::class, "getCustomer"])->name("customer");
//    Route::post("customer/update/{id}", [CustomerController::class, "updatedCustomer"])->middleware('admin_access')->name("update-customer");
//    Route::delete("customer/delete/{id}", [CustomerController::class, "deleteCustomer"])->middleware('admin_access')->name("delete-customer");
//    Route::get("customer/{name}", [CustomerController::class, "getByName"])->name("customerName");
//    Route::get("customer/search/{name}", [CustomerController::class, "getByChar"])->name("searchCustomerName");
//
//    //Supplier
//    Route::post("supplier/add", [SupplierController::class, "saveSupplier"])->middleware('admin_access')->name("add-supplier");
//    Route::get("suppliers", [SupplierController::class, "getSuppliers"])->name("all-suppliers");
//    Route::get("supplier/view/{id}", [SupplierController::class, "getSupplier"])->name("suppliers");
//    Route::post("supplier/update/{id}", [SupplierController::class, "updateSupplier"])->middleware('admin_access')->name("update-supplier");
//    Route::delete("supplier/delete/{id}", [SupplierController::class, "deleteSupplier"])->middleware('admin_access')->name("delete-supplier");
//    Route::get("supplier/{name}", [SupplierController::class, "getByName"])->name("supplierName");
//    Route::get("supplier/search/{name}", [SupplierController::class, "getByChar"])->name("searchSupplierName");
//
//    //Category
//    Route::post("category/add", [CategoryController::class, "saveCategory"])->middleware('admin_access')->name("addCategory");
//    Route::get("categories", [CategoryController::class, "getCategories"])->name("categories");
//    Route::get("category/view/{id}", [CategoryController::class, "getCategory"])->name("category");
//    Route::post("category/update/{id}", [CategoryController::class, "updateCategory"])->middleware('admin_access')->name("updateCategory");
//    Route::delete("category/delete/{id}", [CategoryController::class, "deleteCategory"])->middleware('admin_access')->name("deleteCategory");
//    Route::get("category/{name}", [CategoryController::class, "getByName"])->name("categoryName");
//    Route::get("category/search/{category_name}", [CategoryController::class, "getByChar"])->name("searchCategoryName");
//
//    //Brand
//    Route::post("brand/add", [BrandController::class, "saveBrand"])->middleware('admin_access')->name("addBrand");
//    Route::get("brands", [BrandController::class, "getBrands"])->name("brands");
//    Route::post("brand/update/{id}", [BrandController::class, "updateBrand"])->middleware('admin_access')->name("updateBrand");
//    Route::get("brand/view/{id}", [BrandController::class, "getBrand"])->name("viewBrand");
//    Route::delete("brand/delete/{id}", [BrandController::class, "deleteBrand"])->middleware('admin_access')->name("deleteBrands");
//    Route::get("brand/{name}", [BrandController::class, "getByName"])->name("brandName");
//    Route::get("brand/search/{name}", [BrandController::class, "getByChar"])->name("searchBrandName");
//
//    //Expense
//    Route::get("expense", [ExpenseController::class, "getExpenses"])->name("expenses");
//    Route::get("expense/view/{id_product}", [ExpenseController::class, "getExpense"])->name("viewProductExpenseByID");
//    Route::get("expense/{formDate}/{toDate}", [ExpenseController::class, "getExpenseInRange"])->name("expenseInRange");
//    Route::get("expense/today", [ExpenseController::class, "getTodayExpense"])->name("todayExpense");
//    Route::get("expense/month", [ExpenseController::class, "getMonthExpense"])->name("monthlyExpense");
//    Route::get("expense/year", [ExpenseController::class, "getYearExpense"])->name("yearlyExpense");
//
//    //Income
//    Route::post("income/{id}", [IncomeController::class, "getIncome"])->name("getIncome");
//    Route::get("incomes", [IncomeController::class, "getIncomes"])->name("Income");
//    Route::get("incomes/{formDate}/{toDate}", [IncomeController::class, "getIncomeInRange"])->name("incomeInRange");
//    Route::get("income/today", [IncomeController::class, "getTodayIncome"])->name("todayIncome");
//    Route::get("income/month", [IncomeController::class, "getMonthIncome"])->name("monthlyIncome");
//    Route::get("income/year", [IncomeController::class, "getYearIncome"])->name("yearlyIncome");
//
//    //Import
//    Route::post("import/add/{id}", [ImportController::class, "saveImport"])->middleware('admin_access')->name("addImport");
//    Route::get("imports", [ImportController::class, "getImports"])->name("Imports");
//    Route::get("import/{id}", [ImportController::class, "getImportByProductID"])->name("getImportByProductID");
//
//    //Export
//    Route::post("export/add/{id}", [ExportController::class, "saveExport"])->name("addExport");
//    Route::get("exports", [ExportController::class, "getExports"])->name("Exports");
//    Route::get("export/{id}", [ExportController::class, "getExportByProductID"])->name("getExportByProductID");
//
//    //Revenue
//    Route::get("revenues", [RevenueController::class, "getRevenues"])->name("Revenues");
//    Route::get("revenues/today", [RevenueController::class, "getTodayRevenue"])->name("todayRevenue");
//    Route::get("revenues/month", [RevenueController::class, "getMonthRevenue"])->name("monthlyRevenue");
//    Route::get("revenues/year", [RevenueController::class, "getYearRevenue"])->name("yearlyRevenue");
//    Route::get("revenues/{formDate}/{toDate}", [RevenueController::class, "getRevenueInRange"])->name("revenuesInRange");
//
//    //Get Category
//    Route::get("products/category/{cat_id}", [ProductController::class, "getProductByCategory"])->name("productsByCategory");
//    Route::get("products/category/brand/{cat_id}/{brand_id}", [ProductController::class, "getProductByCategoryAndBrand"])->name("productsByCategoryAndBrand");
//    Route::get("products/category/supplier/{cat_id}/{sup_id}", [ProductController::class, "getProductByCategoryAndSupplier"])->name("productsByCategoryAndSupplier");
//
//    Route::post('/user/{id}', [UserAuthController::class, 'update'])->middleware('admin_access');
//    Route::post('/logout', [UserAuthController::class, 'logout']);
//    Route::get('/user/show', [UserAuthController::class, 'show'])->middleware('admin_access');
//    Route::get("user/search/{user_name}", [UserAuthController::class, "getByChar"])->middleware('admin_access')->name("searchUserName");
//    Route::delete("user/delete/{id}", [UserAuthController::class, "delete"])->middleware('admin_access')->name("delete");
//});
