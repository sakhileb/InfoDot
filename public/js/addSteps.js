var step = 1;
function add_steps() {
    step++;
    var objTo = document.getElementById('steps')
    var divtest = document.createElement("div");
    divtest.innerHTML =
    '<hr class="my-3 text-gray-900"><h4 class="text-gray-900 m-3 text-2xl">Step ' + step +':</h4><hr class="my-3 text-gray-900"><div class="flex flex-wrap -mx-3 mb-6"><div class="w-full md:w-full px-3 mb-6"><label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="solution_title">Heading:</label><input class="appearance-none block w-full input input-bordered" name="solution_heading[]" id="solution_heading" type="text" placeholder="How to......?"> </div> <div class="w-full px-3"><label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">Body:</label><textarea class="textarea h-24 textarea-bordered w-full" name="solution_body[]" placeholder="This solution will help you accomplish 1..2..3..."></textarea></div></div>';
    objTo.appendChild(divtest)
}


