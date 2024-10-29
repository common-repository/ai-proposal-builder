//Custom JS
(function ($) {
  "use strict";

  // Update the URL to the server-side script
  const apiUrl = '/wp-admin/admin-ajax.php';
  //console.log(apiUrl);
  // Make an asynchronous call to get the nonce
  $.ajax({
    type: 'POST',
    url: apiUrl,
    data: {
      action: 'bitcx_aipb_get_nonce', // Use this action to get the nonce
    },
  })
    .done(async function (nonceResponse) {
      // Parse the JSON response
      const nonceData = JSON.parse(nonceResponse);
      // Use the obtained nonce in subsequent OpenAI API calls
      const nonce = nonceData.nonce;


      $('#bitcx_aipb_getSolution').on('click', async function () {
        const client_name = $('#bitcx_aipb_client_name').val();
        const freelance_name = $('#bitcx_aipb_freelance_name').val();
        const job_title = $('#bitcx_aipb_job_title').val();
        const problem = $('#bitcx_aipb_problem_desc').val();
        const loadingSpinner = $('#bitcx_aipb_loadingSpinner');
        const ai_response = $('#bitcx_aipb_ai_response');
        
        if (freelance_name.trim() === '') {
          showError('Freelance Name');
          return;
        }

        if (job_title.trim() === '') {
          showError('Job Title');
          return;
        }

        if (problem.trim() === '') {
          showError('Job Description');
          return;
        }

        ai_response.hide();
        try {
          // Show the loading spinner
          loadingSpinner.show();
          const prompt = `Compose a persuasive freelancing proposal for the position 
      posted by '${client_name}'. Tailor your response to address 
      the specific '${job_title}' and '${problem}' requirements outlined by the client. 
      Highlight your expertise in '${job_title}', emphasizing your ability to meet deadlines, 
      conduct in-depth research, and adapt your writing style to align with the client's needs. 
      Include a brief mention of your portfolio or relevant samples to showcase your work. 
      Include Hello Cleint Name at start and Do not include, regards, name, [Your Full Name] or something similar and also subject is not needed. Format it well.`;

          // Make an asynchronous call to the server-side script
          const response = await jQuery.ajax({
            type: 'POST',
            url: apiUrl,
            data: {
              action: 'bitcx_aipb_ai_response',
              nonce: nonce,
              prompt: prompt,
            },
          });
          // Parse and handle the response
          try {
            const trimmedResponse = response.slice(0, -1);
            const outerResponse = JSON.parse(trimmedResponse);
            if (outerResponse.response) {
              const responseData = JSON.parse(outerResponse.response);

              if (responseData.error) {
                ai_response.show();
                ai_response.text(responseData.error.message);
              } else {
                const solution = responseData.choices[0].message.content.trim();
                // Update the solution field with the generated answer
                $('#bitcx_aipb_solution').val(solution);
              }
            } else {
              console.error('Invalid response format: Missing "response" property.');
            }
          } catch (error) {
            console.error('Error parsing JSON response:', error.message);
            // Handle the error
          }
        } catch (err) {
          ai_response.show();
          ai_response.text('An error occurred while processing the request.');
        } finally {
          // Hide the loading spinner after the request is completed
          loadingSpinner.hide();
        }

        function showError(fieldName) {
          ai_response.show();
          ai_response.text(`Please enter the ${fieldName} before getting a solution.`);
        }
      });

    })
    .fail(function (error) {
      console.error('Error obtaining nonce:', error);
    });

  /**
   * Expand solution textarea height 
   */
  $('#bitcx_aipb_solution').on('focus', function () {
    $(this).css('height', 'auto').css('height', (this.scrollHeight - 22) + 'px');
  });

  $('#bitcx_aipb_solution').on('input', function () {
    $(this).css('height', 'auto').css('height', (this.scrollHeight - 22) + 'px');
  });

  $('#bitcx_aipb_solution').on('blur', function () {
    $(this).css('height', '');
  });

  /**
   * Expand Problem textarea height 
   */
  $('#bitcx_aipb_problem_desc').on('focus', function () {
    $(this).css('height', 'auto').css('height', (this.scrollHeight - 22) + 'px');
  });

  $('#bitcx_aipb_problem_desc').on('input', function () {
    $(this).css('height', 'auto').css('height', (this.scrollHeight - 22) + 'px');
  });

  $('#bitcx_aipb_problem_desc').on('blur', function () {
    $(this).css('height', '');
  });

  /**
   * Get Proposal
   */

  $("#bitcx_aipb_get_proposal").click(function (event) {
    event.preventDefault();

    const clientName = jQuery("#bitcx_aipb_client_name").val();
    const freelance_name = jQuery("#bitcx_aipb_freelance_name").val();
    const porblemDesc = jQuery("#bitcx_aipb_problem_desc").val();
    const solution = jQuery("#bitcx_aipb_solution").val();
    const portfolio_items = jQuery("input[name='bitcx_aipb_portfolioChoice']:checked");
    const testimonial_items = jQuery("input[name='bitcx_aipb_testiChoice']:checked");
    const questions = jQuery("#bitcx_aipb_problem_question").val();
    const cta_item = jQuery("input[name='bitcx_aipb_ctaChoice']:checked");

    if (clientName != "" && porblemDesc != "" && solution != "") {
      let portfolios = [];
      let testimonials = [];
      let ctas = [];
      let portfolio_content = "";
      let testimonial_content = "";
      let cta_content = "";
      let questions_content = "";
      let regards_content ="";
      let finalOutput = "";

      portfolio_items.each(function () {
        portfolios.push([
          $(this).attr("data-title"),
          $(this).attr("data-url"),
          $(this).attr("data-desc"),
          $(this).attr("data-img"),
        ]);
      });

      testimonial_items.each(function () {
        testimonials.push([
          $(this).attr("data-title"),
          $(this).attr("data-desc"),
        ]);
      });

      cta_item.each(function () {
        ctas.push([$(this).attr("data-title"), $(this).attr("data-desc")]);
      });

      if (portfolios.length != 0) {
        portfolio_content = `
            <h4>Portfolio Link: </h4>
            <ul>
              ${Object.keys(portfolios)
            .map(function (key) {
              return (
                "<li>" +
                portfolios[key][0] +
                " : " +
                portfolios[key][1] +
                "</li>"
              );
            })
            .join("")}
            </ul>
          `;
        //  console.log(portfolios);
        let encodedArray = JSON.stringify(portfolios);
        jQuery("#bitcx_aipb_dwnld_brochure").attr("data-prtfolio", encodedArray);
        jQuery("#bitcx_aipb_getSolution").attr("data-prtfolio", encodedArray);
      }

      if (testimonials.length != 0) {
        testimonial_content = `
          <p><strong>Here is what our clients say:</strong></p>
          <div>
            ${Object.keys(testimonials)
            .map(function (key) {
              return (
                "<div>" +

                "<p>" +
                testimonials[key][1] +
                "</p> <span> " +
                testimonials[key][0] +
                "</span>" + "</div>"
              );
            })
            .join("")}
          </div>
        `;
        // console.log(testimonials);
        let testimonialsArray = JSON.stringify(testimonials);

        jQuery("#bitcx_aipb_dwnld_brochure").attr("data-testimonials", testimonialsArray);
      }

      if (questions != "") {
        let questionStr = questions.replace(/(?:\r\n|\r|\n)/g, "<br>");
        questions_content = `
                <p><strong>Could you please confirm below information? </strong></p>
                <div>${questionStr}</div>
              `;
      }

      if (ctas.length != 0) {
        cta_content = `
                <div>
                  ${Object.keys(ctas)
            .map(function (key) {
              return (
                "<p>" + ctas[key][1] + "</p>"
              );
            })
            .join("")}
                </div>
              `;
        console.log(ctas);
        let ctasArray = JSON.stringify(ctas);

        jQuery("#bitcx_aipb_dwnld_brochure").attr("data-ctas", ctasArray);
      }

      finalOutput = `
          <pre>${solution} </pre>
      `;
      regards_content=`
      <p>Best Regards,<br>${freelance_name} </p>
      `;

      if (portfolio_content != "") {
        finalOutput += portfolio_content;
      }

      if (testimonial_content != "") {
        finalOutput += testimonial_content;
      }

      if (questions_content != "") {
        finalOutput += questions_content;
      }

      if (cta_content != "") {
        finalOutput += cta_content;
      }
      if(regards_content !=""){
        finalOutput += regards_content;
      }

      if (portfolios.length != 0) {
        // console.log(portfolios);
        // downloadPDF(portfolios);
        jQuery("#bitcx_aipb_dwnld_brochure").removeClass("bitcx_aipb_pr_d_none");
        // document.querySelector('#pr_output').classList.remove('pr_d_none')
      }
      // console.log( " this is this" + finalOutput);
      if (finalOutput != "") {
        jQuery("body").addClass("overlayed");
        document.querySelector("#bitcx_aipb_pr_result").classList.add('bitcx_aipb_pr_d_none');
        document.querySelector('#bitcx_aipb_pr_output').classList.remove('bitcx_aipb_pr_d_none');
        document.querySelector(".bitcx_aipb_popup_content").innerHTML = finalOutput;
      }
    } else {
      document.querySelector("#bitcx_aipb_pr_result p").innerText = "Please Fill All the Fields";
      document.querySelector("#bitcx_aipb_pr_result").classList.remove('bitcx_aipb_pr_d_none');
    }
  });

  /**
   * Function: Download PDF
   */
  
  function downloadPDF(portfolioArray, testimonialsArray, ctasArray, freelance_name) {
    let pdf = new jsPDF("p", "pt", "letter");

    let specialElementHandlers = {
        "#bypassme": function (element, renderer) {
            return true;
        },
    };

    let margins = {
        top: 40,
        bottom: 40,
        left: 40,
        width: 522,
    };

    // Add introductory text at the top of the PDF
    let introText = `
      <div>
        <p>Hi,</p>
        <p>I am attaching the screenshots of the similar projects that I have created, and some client reviews.</p>
      </div>
    `;
    pdf.fromHTML(introText, margins.left, margins.top, {
        width: margins.width,
        elementHandlers: specialElementHandlers,
    });

    // Add some space after the intro text
    let currentPageHeight=0; // Adjust this value to add more space

    // Function to compress an image
    function compressImage(src, quality, callback) {
        let img = new Image();
        img.onload = function () {
            let canvas = document.createElement('canvas');
            canvas.width = this.naturalWidth;
            canvas.height = this.naturalHeight;
            canvas.getContext('2d').drawImage(this, 0, 0);
            callback(canvas.toDataURL('image/jpeg', quality));
        };
        img.src = src;
    }

    // Add portfolio items
    for (let i = 0; i < portfolioArray.length; i++) {
      if(i==0){
        currentPageHeight = margins.top + 60; // Adjust starting height to account for intro text
      } else{
        currentPageHeight = margins.top; // Adjust starting height to account for intro text
      }
        let recordTitle = portfolioArray[i][0];
        let recordURL = portfolioArray[i][1];
        let recordDescription = portfolioArray[i][2];
        let recordImageURL = portfolioArray[i][3];

        (function(currentIndex, currentHeight) {
            compressImage(recordImageURL, 0.8, function (compressedImageURL) {
                let htmlContent = `
                    <div class="bitcraftx_aipb_single-portfolio-item bitcraftx_aipb_pagebreak">
                      <h3>${recordTitle}</h3>
                      ${recordURL ? `<a href="${recordURL}" target="_blank">${recordURL}</a>` : ''}
                      <p>${recordDescription}</p>
                      <div style="text-align: center;">
                        <img src="${compressedImageURL}" width="500" style="margin-left:auto;" />
                      </div>
                    </div>
                `;

                pdf.fromHTML(
                    htmlContent,
                    margins.left,
                    currentHeight,
                    {
                        width: margins.width,
                        elementHandlers: specialElementHandlers,
                    },
                    function (dispose) {
                        // Add page if necessary
                        if (currentIndex < portfolioArray.length - 1) {
                            pdf.addPage();
                            currentPageHeight = margins.top; // Reset height for new page
                        } else {
                            // Add testimonials and CTAs after portfolio items
                            pdf.addPage();
                            addTestimonialsAndCTAs(pdf, testimonialsArray, ctasArray, margins, specialElementHandlers, freelance_name);
                        }
                    },
                    margins
                );
            });
        })(i, currentPageHeight);

        currentPageHeight += 0; // Adjust height for next portfolio item, ensure enough space
    }
}

function addTestimonialsAndCTAs(pdf, testimonialsArray, ctasArray, margins, specialElementHandlers, freelance_name) {
    let currentPageHeight = margins.top;

    // Add testimonials
    let testimonialIntro = `
      <div>
        <p>Here is what our clients have to say about me:</p>
      </div>
    `;
    pdf.fromHTML(testimonialIntro, margins.left, currentPageHeight, {
        width: margins.width,
        elementHandlers: specialElementHandlers,
    });
    currentPageHeight += 40; // Adjust height for testimonial intro

    for (let i = 0; i < testimonialsArray.length; i++) {
        let testimonial = testimonialsArray[i];

        let testimonialContent = `
          <div class="bitcraftx_aipb_testimonial-item" style="font-style: italic;">
            <p>${testimonial}</p>
          </div>
        `;

        pdf.fromHTML(
            testimonialContent,
            margins.left,
            currentPageHeight,
            {
                width: margins.width,
                elementHandlers: specialElementHandlers,
            },
            function (dispose) {
                // No page break here, handled above
            },
            margins
        );

        currentPageHeight += 40; // Adjust height for next testimonial
    }

    // Add CTAs
    for (let i = 0; i < ctasArray.length; i++) {
        let cta = ctasArray[i][1]; // Adjusted to get the CTA message

        let ctaContent = `
          <div class="bitcraftx_aipb_cta-item">
            <p>${cta}</p>
            <p>${freelance_name}</p>
          </div>
        `;

        pdf.fromHTML(
            ctaContent,
            margins.left,
            currentPageHeight,
            {
                width: margins.width,
                elementHandlers: specialElementHandlers,
            },
            function (dispose) {
                // No page break here, handled above
            },
            margins
        );

        currentPageHeight += 40; // Adjust height for next CTA
    }

    // Save the final PDF
    pdf.save("Proposal.pdf");
}

  


  /**
   * Download PDF on Button Click
  */

  jQuery("body").on("click", "#bitcx_aipb_dwnld_brochure", function () {
    const dataArrayAsString = $(this).attr('data-prtfolio');
    const datatestimonials = $(this).attr('data-testimonials');
    const datactas = $(this).attr('data-ctas');
    const retrievedArray = JSON.parse(dataArrayAsString);
    const testimonialsArray = JSON.parse(datatestimonials);
    const ctasArray = JSON.parse(datactas);
    const freelance_name = $('#bitcx_aipb_freelance_name').val();
    downloadPDF(retrievedArray, testimonialsArray, ctasArray,freelance_name);
  });

  /**
   * Display Portfolio Items w.r.t Category
  */
  $("#bitcx_aipb_portfolio_cats").change(function (event) {
    // Listen for the change event on the select element
    var selectedCategory = $(this).val();
    if (selectedCategory === "") {
      // If "All Categories" is selected, show all items
      $("div[data-cat]").show();
    } else {
      // Otherwise, hide all items and show the selected category item
      $("div[data-cat]").hide();
      $('div[data-cat*="' + selectedCategory + '"]').show();
    }
    showcontroller();
  });

  function showcontroller() {
    var swiperContainer = document.querySelector('.bitcx_swiper');
    var slides = swiperContainer.querySelectorAll('.swiper-slide');
    var visibleSlides = 0;
    slides.forEach(function (slide) {
      var computedStyle = window.getComputedStyle(slide);
      if (computedStyle.display !== 'none' && computedStyle.visibility !== 'hidden') {
        visibleSlides++;
      }
    });
    console.log(visibleSlides);
    if (visibleSlides <= 3) {
      $(".swiper-button-next").hide();
      $(".swiper-button-prev").hide();

    } else {
      $(".swiper-button-next").show();
      $(".swiper-button-prev").show();
    }
  }
  showcontroller();

  /**
   * Display Portfolio Items w.r.t Category
  */
  var swiper = new Swiper(".bitcx_swiper", {
    slidesPerView: 1,
    spaceBetween: 30,
    freeMode: true,
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
    loop: true,
    // Responsive breakpoints
    breakpoints: {
      480: {
        slidesPerView: 2,
        spaceBetween: 20,
      },
      900: {
        slidesPerView: 3,
        spaceBetween: 30,
      },
    }
  });

  /**
   * Output
  */
  document.querySelector('.bitcx_aipb_cross_icon').addEventListener('click', function () {
    jQuery("body").removeClass("overlayed");
    document.querySelector('#bitcx_aipb_pr_output').classList.add('bitcx_aipb_pr_d_none');
    document.getElementById("bitcx_aipb_copy_button").innerText = "Click To Copy";
  });

  /**
   * Click to Copy Functionality
  */

  document.getElementById("bitcx_aipb_copy_button").addEventListener("click", function () {
    // Get the text content from the div element
    var textToCopy = document.getElementById("bitcx_aipb_popup_content").innerText;

    // Create a temporary textarea to copy the text to clipboard
    var tempTextArea = document.createElement("textarea");
    tempTextArea.value = textToCopy;
    document.body.appendChild(tempTextArea);

    // Select the text in the temporary textarea
    tempTextArea.select();
    tempTextArea.setSelectionRange(0, 99999); // For mobile devices

    // Copy the selected text to clipboard
    document.execCommand("copy");

    // Remove the temporary textarea
    document.body.removeChild(tempTextArea);

    // Show a message to the user indicating successful copy (optional)
    // alert("Text copied to clipboard!");
    document.getElementById("bitcx_aipb_copy_button").innerText = "Copied";
  });



})(jQuery);

