import $ from "jquery"
import axios from "axios"

class CaseStudies {
  //1. INITALIZATION
  constructor() {
    // Stop executing program if there is no "all_case_studies" ID
    if ($("#all_case_studies").length == 0) return

    this.allCaseStudies = $("#all_case_studies")
    this.csPagination = $("#cs_pagination")
    this.csPaginationLink = $(".page-numbers")
    this.caseStudyDropdown = $(".case_study_dropdown")
    this.caseStudies = $("#case_studies")

    this.events()
  }

  // 2. EVENTS.

  events() {
    $(document).on("click", "a.page-numbers", this.loadCaseStudies.bind(this)) // this refers to the class (".page-numbers")
    this.caseStudyDropdown.on("change", this.loadCaseStudiesDropdown.bind(this))
  }

  // 3. METHODS.

  getUrlParameters(param) {
    return new URLSearchParams(window.location.search).get(param)
  }

  loadCaseStudiesDropdown(e) {
    let $cat = $(e.target[e.target.selectedIndex]).data("cat")
    let $tax = $(e.target[e.target.selectedIndex]).data("tax")

    let $case_base_url = this.allCaseStudies.data("case_base_url")

    let $mod_url = $case_base_url + "?cat=" + $cat + "&tax=" + $tax

    if (typeof $cat == "undefined") {
      $mod_url = $case_base_url
    }
    window.history.pushState("", "", $mod_url)

    this.caseStudyDropdown.val("")

    $(e.target).val($cat)

    let params = new URLSearchParams()
    params.append("current_page", 1) // will make it dynamic later.

    this.ajaxRequest(params)
  }

  loadCaseStudies(e) {
    e.preventDefault()
    let $this = $(e.target.closest("a.page-numbers"))

    let currentPageUrl = $this.attr("href")
    let pageNo = currentPageUrl.split("/")
    let nextPageNo = pageNo[pageNo.length - 2]
    window.history.pushState("", "", currentPageUrl)

    let params = new URLSearchParams()
    params.append("current_page", nextPageNo) // will make it dynamic later.

    this.ajaxRequest(params)
  }

  ajaxRequest(params) {
    params.append("ajax", "1")
    params.append("action", "load_more_posts")
    if (this.getUrlParameters("tax")) {
      params.append("tax", this.getUrlParameters("tax")) // will make it dynamic later.
      params.append("cat", this.getUrlParameters("cat")) // will make it dynamic later.
    }
    this.csPagination.length ? this.csPagination.remove() : ""
    this.caseStudies.html("Loading....")
    window.scrollTo({ top: 0, behavior: "smooth" })
    axios.post(pmapiAdditionalData.pmapi_app_root + "/wp-admin/admin-ajax.php", params).then((res) => {
      this.caseStudies.html("").html(res.data.data)
    })
  }
}

export default CaseStudies
