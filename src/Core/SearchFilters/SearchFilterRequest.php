<?php
namespace W2w\Lib\Apie\Core\SearchFilters;

use Pagerfanta\Exception\OutOfRangeCurrentPageException;
use Pagerfanta\Pagerfanta;
use Psr\Http\Message\ServerRequestInterface;
use W2w\Lib\Apie\Exceptions\InvalidPageLimitException;
use W2w\Lib\Apie\Exceptions\PageIndexShouldNotBeNegativeException;

/**
 * Request parameters for get all.
 */
final class SearchFilterRequest
{
    /**
     * @var int
     */
    private $pageIndex;

    /**
     * @var int
     */
    private $numberOfItems;

    /**
     * @var mixed[]
     */
    private $searches;

    /**
     * @param int $pageIndex
     * @param int $numberOfItems
     * @param mixed[] $searches
     */
    public function __construct(
        int $pageIndex = 0,
        int $numberOfItems = 20,
        array $searches = []
    ) {
        if ($pageIndex < 0) {
            throw new PageIndexShouldNotBeNegativeException();
        }
        if ($numberOfItems < 1) {
            throw new InvalidPageLimitException();
        }
        $this->pageIndex = $pageIndex;
        $this->numberOfItems = $numberOfItems;
        $this->searches = $searches;
    }

    /**
     * @return mixed[]
     */
    public function getSearches(): array
    {
        return $this->searches;
    }

    public function updatePaginator(Pagerfanta $pager)
    {
        $pager->setAllowOutOfRangePages(true);
        $pager->setMaxPerPage($this->getNumberOfItems());
        $pager->setCurrentPage($this->getPageIndex() + 1);
    }

    /**
     * @return int
     */
    public function getPageIndex(): int
    {
        return $this->pageIndex;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->pageIndex * $this->numberOfItems;
    }

    /**
     * @return int
     */
    public function getNumberOfItems(): int
    {
        return $this->numberOfItems;
    }

    /**
     * Creates SearchFilterRequest from a PSR request.
     *
     * @param ServerRequestInterface $psrRequest
     * @return SearchFilterRequest
     */
    static public function createFromPsrRequest(ServerRequestInterface $psrRequest): SearchFilterRequest
    {
        $params = $psrRequest->getQueryParams();
        $pageIndex = (int) ($params['page'] ?? 0);
        $limit = (int) ($params['limit'] ?? 20);
        $searches = $params;
        unset($searches['page']);
        unset($searches['limit']);
        return new SearchFilterRequest($pageIndex, $limit, $searches);
    }

    /**
     * Filters out searches that are not in the search filter and converts them to the right type.
     *
     * @param SearchFilter $searchFilter
     * @return SearchFilterRequest
     */
    public function applySearchFilter(SearchFilter $searchFilter): self
    {
        $params = $this->searches;
        $searches = [];
        foreach ($searchFilter->getAllPrimitiveSearchFilter() as $name => $primitive) {
            if (isset($params[$name])) {
                $searches[$name] = $primitive->convert($params[$name]);
            }
        }
        $this->searches = $searches;
        return $this;
    }
}
