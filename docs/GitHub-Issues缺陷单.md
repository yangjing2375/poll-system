# 缺陷 Issue 清单

本文件与 `docs/缺陷清单.xlsx` 一一对应，仓库 Issues 按同样内容登记，便于按优先级跟踪与回归。

| 编号 | 标题 | 严重程度 | 优先级 | 状态 |
| --- | --- | --- | --- | --- |
| BUG-01 | 多选投票必然失败（唯一键冲突） | 严重 | P0 | 已关闭 |
| BUG-02 | 已禁用的投票仍可提交 | 高 | P1 | 已关闭 |
| BUG-03 | 已结束的投票仍可提交 | 高 | P1 | 已关闭 |
| BUG-04 | 多选数量不校验 max_options | 高 | P1 | 已关闭 |
| BUG-05 | 单选投票接受多个选项 | 中 | P2 | 已关闭 |
| BUG-06 | 投票写入无事务，出现部分写入 | 高 | P1 | 已关闭 |
| BUG-07 | page 参数为负数导致 500 | 中 | P2 | 已关闭 |
| BUG-08 | limit 无上限，一次可拉取整表 | 中 | P2 | 已关闭 |
| BUG-09 | 错误响应回显数据库异常信息 | 中 | P2 | 已关闭 |
| BUG-10 | 头像上传仅校验客户端 MIME | 中 | P2 | 已关闭 |
| BUG-11 | 编辑投票删除全部选项导致历史票数清零 | 高 | P1 | 待修复 |
| BUG-12 | 我的投票记录中管理员创建的投票创建者显示错误 | 中 | P2 | 待修复 |
| BUG-13 | 不存在的投票 ID 触发 PHP Warning 并污染 JSON 响应 | 中 | P2 | 已关闭 |

---

## BUG-01 多选投票必然失败（唯一键冲突）

- 标签：`bug` `P0`
- 状态：已关闭

**现象**：对多选投票提交两个以上选项时，接口返回 500 级错误，提示 `Duplicate entry '2-2' for key 'uk_poll_user'`。

**复现步骤**
1. 使用未投过票的用户登录；
2. `POST /api/vote.php`，body：`{"poll_id":2,"option_ids":[5,6]}`；
3. 查看响应。

**预期**：返回 `status=success`，两条投票记录写入，对应选项票数各 +1。
**实际**：返回 `status=error`，并暴露数据库唯一键名。

**原因**：`poll_votes` 的唯一键为 `(poll_id, user_id)`，循环插入第二条即冲突。
**修复**：唯一键改为 `(poll_id, user_id, option_id)`，接口层再校验「一票一投」。

---

## BUG-02 已禁用的投票仍可提交

- 标签：`bug` `P1`
- 状态：已关闭

**复现步骤**
1. 将 poll 3 的 `is_active` 置 0；
2. 登录用户 `POST /api/vote.php`，`poll_id=3`。

**预期**：返回 `status=error`，提示该投票已禁用。
**实际**：返回 `status=success`，投票成功写入。
**原因**：`vote.php` 未校验 `is_active`。
**修复**：提交前查询投票状态，禁用则拒绝。

---

## BUG-03 已结束的投票仍可提交

- 标签：`bug` `P1`
- 状态：已关闭

**复现步骤**
1. 构造 `end_time` 为昨天的投票；
2. 登录用户提交该投票。

**预期**：返回 `status=error`，提示该投票已结束。
**实际**：返回 `status=success`。
**原因**：`vote.php` 未比较 `end_time` 与当前时间。
**修复**：投票接口增加时间校验；创建与编辑接口同时补充「结束时间不能早于当前时间」的校验。

---

## BUG-04 多选数量不校验 max_options

- 标签：`bug` `P1`
- 状态：已关闭

**复现步骤**
1. poll 2 为多选且 `max_options=2`；
2. 提交 `option_ids=[4,5,6]`。

**预期**：返回 `status=error`，提示最多只能选择 2 项。
**实际**：返回 `status=success`，3 项全部写入。
**修复**：`vote.php` 增加选项数量上限校验；创建/编辑接口增加 `max_options ≤ 有效选项数` 约束。

---

## BUG-05 单选投票接受多个选项

- 标签：`bug` `P2`
- 状态：已关闭

**复现步骤**
1. poll 1 为单选；
2. 提交 `option_ids=[1,2]`。

**预期**：返回 `status=error`，提示最多只能选择 1 项。
**实际**：返回 `status=success`，两个选项都计票。
**修复**：单选时按 1 项上限校验。

---

## BUG-06 投票写入无事务，出现部分写入

- 标签：`bug` `P1`
- 状态：已关闭

**复现步骤**
1. 提交多选投票，构造第 2 条插入失败（如重复选项）；
2. 检查 `poll_votes` 与 `poll_options.vote_count`。

**预期**：要么全部成功，要么全部回滚。
**实际**：第 1 条记录已写入且票数 +1，第 2 条失败，数据不一致。
**修复**：写入循环包在事务中，异常时 `rollBack` 并返回统一失败提示。

---

## BUG-07 page 参数为负数导致 500

- 标签：`bug` `P2`
- 状态：已关闭

**复现步骤**
1. 管理员登录；
2. `GET /api/admin_polls.php?page=-1&limit=10`。

**预期**：正常返回第 1 页数据。
**实际**：出现 SQL 错误并返回 500。
**修复**：`page` 下限收敛为 1（`admin_polls.php`、`admin_users.php`）。

---

## BUG-08 limit 无上限，一次可拉取整表

- 标签：`bug` `P2`
- 状态：已关闭

**复现步骤**
1. 管理员登录；
2. `GET /api/admin_users.php?limit=99999`。

**预期**：`limit` 收敛到合理上限，返回条数 ≤ 100。
**实际**：按 99999 条查询，全表返回。
**修复**：`limit` 上限收敛为 100，下限为 1。

---

## BUG-09 错误响应回显数据库异常信息

- 标签：`bug` `P2`
- 状态：已关闭

**复现步骤**
1. 制造异常（如非法分页、唯一键冲突）；
2. 观察响应 `message`。

**预期**：返回业务提示，不含 SQL 语句、索引名或堆栈。
**实际**：`message` 直接拼接 `$e->getMessage()`，暴露 SQL 与表结构。
**修复**：`vote`、`admin_polls`、`admin_users`、`admin_statistics`、`get_polls`、`get_poll_results`、`get_user_votes`、`register` 统一改为业务提示，异常写入 `Logger`。

---

## BUG-10 头像上传仅校验客户端 MIME

- 标签：`bug` `P2`
- 状态：已关闭

**复现步骤**
1. 准备文本文件 `1.txt`，改名为 `1.jpg`；
2. 上传时把 `Content-Type` 设为 `image/jpeg`。

**预期**：服务端按文件真实内容判断，拒绝非图片。
**实际**：上传成功并写入头像字段。
**修复**：改用 `getimagesize()` 检测真实图片类型，不再信任 `$_FILES['type']`。

---

## BUG-11 编辑投票删除全部选项导致历史票数清零

- 标签：`bug` `P1`
- 状态：待修复

**复现步骤**
1. poll 1 已有 2 票；
2. `POST /api/admin_polls.php`，`action=edit`，仅修改标题。

**预期**：标题更新，原有选项与票数保持不变。
**实际**：编辑时先 `DELETE FROM poll_options` 再重建，`poll_votes` 因外键级联被删除，票数归零。

**修复方案**：按 `option_id` 增量更新——保留原选项，仅新增或修改文本；删除选项前校验是否已有投票，有投票则禁止删除或改为「下架选项」。

---

## BUG-12 我的投票记录中管理员创建的投票创建者显示错误

- 标签：`bug` `P2`
- 状态：待修复

**复现步骤**
1. `alice` 登录并投过 poll 2（由管理员创建）；
2. `GET /api/get_user_votes.php`。

**预期**：`creator_name` 为 `admin`。
**实际**：`creator_name` 显示为 `alice`。

**原因**：`polls.creator_id` 的外键指向 `users`，管理员创建投票时写入的是 `admin_id`；当管理员 id 与某个用户 id 相同时，查询中的 `JOIN users` 会错误匹配到该普通用户。

**修复方案**：`get_user_votes.php` 改为 `LEFT JOIN users + LEFT JOIN admins`；根治需给 `polls` 增加 `creator_type` 字段，并在写入时记录创建者类型。

---

## BUG-13 不存在的投票 ID 触发 PHP Warning 并污染 JSON 响应

- 标签：`bug` `P2`
- 状态：已关闭

**复现步骤**
1. 登录后 `GET /api/get_poll_results.php?poll_id=999`；
2. 查看响应体。

**预期**：响应为纯 JSON，`success=false`，`message=投票不存在`。
**实际**：JSON 前输出 `<b>Warning</b>: Trying to access array offset on value of type bool`，其中包含服务器绝对路径，前端 `JSON.parse` 直接报错。

**原因**：查询结果为空时先访问 `$poll['is_anonymous']`，之后才判断 `$poll` 是否为空。
**修复**：先判空再读取字段；生产环境关闭 `display_errors`，异常统一写入日志。
