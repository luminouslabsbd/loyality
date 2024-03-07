@extends('partner.layouts.default')

@section('page_title', trans('common.partner') . config('default.page_title_delimiter') . trans('common.dashboard') . config('default.page_title_delimiter') . config('default.app_name'))

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<section class="">
    
    <div class="relative dark:bg-gray-800 m-0 px-5">

     <div class="px-0 py-3">
        <div class="w-full flex items-center space-x-3 my-3">
           <h5 class="dark:text-white font-semibold flex items-center">
              <svg class="inline-block w-5 h-5 mr-2 dark:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                 <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z"></path>
                 <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75zM6.75 16.5h.75v.75h-.75v-.75zM16.5 6.75h.75v.75h-.75v-.75zM13.5 13.5h.75v.75h-.75v-.75zM13.5 19.5h.75v.75h-.75v-.75zM19.5 13.5h.75v.75h-.75v-.75zM19.5 19.5h.75v.75h-.75v-.75zM16.5 16.5h.75v.75h-.75v-.75z"></path>
              </svg>
              Campaign List
           </h5>
          
        </div>
        <div class="ll-main-content-container w-full flex flex-row items-center justify-between gap-x-7">
           <div class="w-full">
              <form class="flex items-center">
                 <label for="tableDataDefinition-search" class="sr-only">Search</label>
                 <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                       <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path>
                       </svg>
                    </div>
                    <input type="search" name="search" id="tableDataDefinition-search" class="bg-gray-50 text-gray-900 text-sm block w-full pl-10 border-0 p-2 dark:bg-gray-700 dark:placeholder-gray-400 dark:text-white" placeholder="Search" value="" style="border-radius: 100vmax;">
                 </div>
              </form>
           </div>
           <div class="flex flex-row items-center justify-between gap-x-5">
              <a href="{{route('luminouslabs::partner.campain.create')}}" class="whitespace-nowrap w-fit flex text-sm items-center btn-primary ll-primary-btn">
                 <svg class="ll-plus-icon me-2" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M7.75 5.5V10M10 7.75H5.5M14.5 7.75C14.5 11.4779 11.4779 14.5 7.75 14.5C4.02208 14.5 1 11.4779 1 7.75C1 4.02208 4.02208 1 7.75 1C11.4779 1 14.5 4.02208 14.5 7.75Z" stroke="#FAFAFA" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                 </svg>
                 Add new item
              </a>
           </div>
        </div>
     </div>
     <div class="overflow-x-auto">
        <form method="POST" id="formDataDefinition">
           <input type="hidden" name="_token" value="jPi21vaDu3Zyn6EwaGV3e9umi5PDkcuwCGIINyct" autocomplete="off">            
           <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400" id="tableDataDefinition">
              <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                 <tr>
                  
                     <th scope="col" class=" py-3  px-6 whitespace-nowrap">
                          Campaign Name
                     </th>

                     <th scope="col" class=" py-3  px-6 whitespace-nowrap">
                        Card Number
                     </th>

                     <th scope="col" class=" py-3  px-6 whitespace-nowrap">
                        Campaign Code
                     </th>

                     <th scope="col" class=" py-3  px-6 whitespace-nowrap">
                        Price Check
                     </th>

                     <th scope="col" class=" py-3  px-6 whitespace-nowrap">
                        Point Check
                     </th>

                    
                    <th scope="col" class=" py-3  px-6 whitespace-nowrap text-center lg-only:hidden">
                        Active
                    </th>
                 
                    <th scope="col" class="px-6 py-3 text-right">
                       Actions
                    </th>
                 </tr>
              </thead>
              
              <tbody>
                @foreach ($campainData as $data)
                    
                  <tr class="border-b dark:border-gray-700 hover:bg-gray-100 bg-white dark:bg-gray-800 dark:hover:bg-gray-900/50" :class="selected[0] ? 'bg-gray-200 hover:bg-gray-200 dark:bg-gray-900 dark:hover:bg-800' : 'bg-white dark:bg-gray-800 dark:hover:bg-gray-900/50'">
                    
                    <td>  {{ $data->name }}</td>
                    <td>  {{ $data->card_id }}</td>
                    <td>  {{ $data->campain_code }}</td>
                    <td>  {{ $data->price_check }}</td>
                    <td>  {{ $data->point_check }}</td>
                    <td>  {{ $data->status == 1 ? 'Active' : 'Hide' }}</td>

                     <td class="px-6 py-4 text-right">
                       <div class="flex flex-nowrap justify-end space-x-2">
                          <div data-tooltip-target="results-tooltip" data-tooltip-placement="top" class="null">
                             <a href="{{route('luminouslabs::partner.campain.view', $data->id)}}" data-fb="tooltip" class="ll-action-btn ll-primary whitespace-nowrap items-center flex px-2 py-2 text-xs border focus:outline-none font-medium rounded text-center">
                                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                   <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path>
                                </svg>
                             </a>
                          </div>
                          <div data-tooltip-target="results-tooltip-1701717886709" data-tooltip-placement="top" class="null">
                             <a href="{{ route('luminouslabs::partner.campain.edit', $data->id) }}" data-fb="tooltip" class="ll-action-btn ll-warning whitespace-nowrap items-center flex px-2 py-2 text-xs focus:outline-none font-medium rounded text-center">
                                <svg class="h-3.5 w-3.52" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                   <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"></path>
                                </svg>
                             </a>
                          </div>
                          <div data-tooltip-target="results-tooltip" data-tooltip-placement="top" class="null">
                             <a href="javascript:void(0);" data-fb="tooltip" class="ll-action-btn ll-danger whitespace-nowrap items-center flex px-2 py-2 text-xs focus:outline-none font-medium rounded text-center" @click="deleteItem( '{{$data->id}}', '{{$data->name}}')">
                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                   <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                             </a>
                          </div>
                       </div>
                     </td>
                  </tr>
            
                @endforeach

              </tbody>
           </table>
        </form>
     </div>
     
     
     </div>
     
     <script>

      function deleteItem(id, item) {
         if (item == null) item = "{{ trans('common.this_item') }}";

         // Use a simple JavaScript confirm dialog
         const confirmation = confirm('Are you sure you want to delete ' + item + '?');

         if (confirmation) {
            // If the user confirms, submit the form using fetch
            var url = '{{ route("luminouslabs::partner.campain.delete", ["id" => ":id"]) }}';
            url = url.replace(':id', id);

            fetch(url, {
                  method: 'POST',
                  headers: {
                     'X-CSRF-TOKEN': '{{ csrf_token() }}',
                     'Content-Type': 'application/x-www-form-urlencoded',
                  },
            })
            .then(response => {
                  if (!response.ok) {
                     throw new Error('Network response was not ok');
                  }
                  window.location.reload(); // Reload the page
            })
            .catch(error => {
                  // Handle errors
                  console.error('There was a problem with the fetch operation:', error);
            });
         }
      }

  </script>

</section>




@stop
