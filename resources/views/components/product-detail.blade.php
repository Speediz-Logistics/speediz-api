<div>
    <!-- Waste no more time arguing what a good man should be, be one. - Marcus Aurelius -->
    <div>
        <!-- component -->
        <section class="text-gray-700 body-font overflow-hidden bg-white">
            <div class="container py-10 mx-auto border-b-2 border-gray-200 mb-5">
                <div class="lg:w-5/5 mx-auto flex flex-wrap">
                    <img alt="ecommerce" class="lg:w-1/3 w-full object-cover object-center rounded border border-gray-200"
                        src="{{$product->product_image}}">
                    <div class="lg:w-1/2 w-full lg:pl-32 lg:py-6 mt-6 lg:mt-0">
                        <h2 class="text-sm title-font text-gray-500 tracking-widest">Code : {{$product->product_code}}</h2>
                        <h1 class="text-gray-900 text-3xl title-font font-medium mb-1">{{$product->product_name}}</h1>
                        <h2 class="mt-6 mb-2 title-font font-medium">Description : </h2>
                        <p class="leading-relaxed ms-5">{{$product->description}}</p>
                        <div class="flex mt-6 items-center pb-5 border-b-2 border-gray-200 mb-5">
                            <div class="flex">
                            </div>
                        </div>
                        <div class="flex">
                            <span class="title-font font-medium text-2xl text-gray-900">Price : ${{$product->export_price}}</span>
                            <span
                                class="flex ml-auto text-white bg-gray-700 border-0 py-2 px-6 rounded">Total in Stock : {{$product->product_quantity}} Unit</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="second lg:mx-24 ">
                <div class="lg:w-5/5 mx-auto flex flex-wrap">
                    <div class="specification lg:w-2/5 lg:px-5">
                        <h3 class="font-bold text-lg ">Specification</h3>
                        <div class="content  mr-5 px-5 py-5 mt-5">
                            <table style="font-family: arial, sans-serif; border-collapse: collapse; width: 100%;">
                                <tr style="border: 1px solid #dddddd; text-align: left; padding: 15px;">
                                    <td style="border: 1px solid #dddddd; text-align: left; padding: 15px;">Product Warehouse : </td>
                                    <td style="border: 1px solid #dddddd; text-align: left; padding: 15px;">{{$product->product_garage}}</td>
                                </tr>
                                <tr style="border: 1px solid #dddddd; text-align: left; padding: 15px;" >
                                    <td style="border: 1px solid #dddddd; text-align: left; padding: 15px;">Product Origin : </td>
                                    <td style="border: 1px solid #dddddd; text-align: left; padding: 15px;">{{$product->product_route}}</td>
                                </tr>
                                <tr style="border: 1px solid #dddddd; text-align: left; padding: 15px;" >
                                    <td style="border: 1px solid #dddddd; text-align: left; padding: 15px;">Expire Date : </td>
                                    <td style="border: 1px solid #dddddd; text-align: left; padding: 15px;">{{$product->expire_date}}</td>
                                </tr>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

</div>
